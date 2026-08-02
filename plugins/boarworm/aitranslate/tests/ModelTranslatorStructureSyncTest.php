<?php

require_once __DIR__ . '/fixtures/TranslatorTestCase.php';

use Tailor\Models\StructureRecord;
use Boarworm\AiTranslate\Classes\ModelTranslator;

/**
 * Regression tests for the October 4.3 Multisite::propagateHasManyRelation()
 * behavior: saving a multisite:sync parent with propagate:true auto-clones
 * missing nested-tree children to other sites, which used to make
 * ModelTranslator's create-mode "already exists" check wrongly skip real
 * translation. See project_october_4_3_multisite_propagation.md.
 */
class ModelTranslatorStructureSyncTest extends TranslatorTestCase
{
    public function testMultisiteSyncTranslatesTopLevelAndRepeaterFields()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\TestStructureSync', [
            'title' => 'Hello',
            'description' => 'A test description',
        ]);

        $item = $source->items()->create(['label' => 'First', 'icon' => 'icon-star']);
        $item->sub_items()->create(['sub_label' => 'Sub One']);

        $this->fakeTranslation([
            'title' => 'Hallo',
            'description' => 'Eine Testbeschreibung',
            'items' => [
                ['label' => 'Erste', 'sub_items' => [['sub_label' => 'Unter Eins']]],
            ],
        ]);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');

        $this->assertTrue($result['success']);
        $this->assertFalse($result['skipped'] ?? false, 'must not skip - the child has never actually been translated');

        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));
        $this->assertSame('Hallo', $target->title);
        $this->assertSame('Erste', $target->items[0]->label);
        $this->assertSame('Unter Eins', $target->items[0]->sub_items[0]->sub_label);
    }

    /**
     * This is the actual bug: a plain, non-propagating save on the source
     * model must NOT auto-clone it to other sites. If it did, the plugin's
     * own create-mode "already exists" check would treat that untranslated
     * clone as "already translated" and skip it forever.
     */
    public function testPlainSaveDoesNotAutoCloneToOtherSites()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\TestStructureSync', [
            'title' => 'Hello',
        ]);

        $source->title = 'Hello Updated';
        $source->save();

        $existsOnTarget = $source->findForSite($this->targetSite->id);

        $this->assertNull($existsOnTarget, 'a plain save() must not silently create an untranslated clone on other sites');
    }

    /**
     * A save with the 'propagate' option (what October's FormController
     * update_onSave() uses) DOES auto-clone missing children on a sync
     * parent - this is October core behavior we deliberately avoid
     * triggering from AiTranslate::onSaveForm(), documented here so the
     * regression is obvious if AiTranslate ever goes back to using it.
     */
    public function testPropagateSaveDoesAutoCloneToOtherSites()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\TestStructureSync', [
            'title' => 'Hello',
        ]);

        $source->save(['propagate' => true]);

        $existsOnTarget = $source->findForSite($this->targetSite->id);

        $this->assertNotNull($existsOnTarget, 'documents October 4.3 core behavior - propagate:true DOES auto-clone');
    }
}
