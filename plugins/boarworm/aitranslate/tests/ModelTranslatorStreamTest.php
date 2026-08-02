<?php

require_once __DIR__ . '/fixtures/TranslatorTestCase.php';

use Tailor\Models\StreamRecord;
use Boarworm\AiTranslate\Classes\ModelTranslator;

class ModelTranslatorStreamTest extends TranslatorTestCase
{
    public function testMultisiteTrueTranslatesTopLevelAndRepeaterFields()
    {
        $source = $this->createEntry(StreamRecord::class, 'Boarworm\AiTranslate\TestStream', [
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

        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));
        $this->assertSame('Hallo', $target->title);
        $this->assertSame('Erste', $target->items[0]->label);
        $this->assertSame('Unter Eins', $target->items[0]->sub_items[0]->sub_label);
    }

    public function testMultisiteSyncPlainSaveDoesNotAutoCloneToOtherSites()
    {
        $source = $this->createEntry(StreamRecord::class, 'Boarworm\AiTranslate\TestStreamSync', ['title' => 'Hello']);

        $source->title = 'Hello Updated';
        $source->save();

        $this->assertNull($source->findForSite($this->targetSite->id));
    }

    public function testMultisiteSyncTranslates()
    {
        $source = $this->createEntry(StreamRecord::class, 'Boarworm\AiTranslate\TestStreamSync', ['title' => 'Hello']);

        $this->fakeTranslation(['title' => 'Hallo']);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');

        $this->assertTrue($result['success']);
        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));
        $this->assertSame('Hallo', $target->title);
    }
}
