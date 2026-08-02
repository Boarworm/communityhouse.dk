<?php

require_once __DIR__ . '/fixtures/TranslatorTestCase.php';

use Tailor\Models\StructureRecord;
use Boarworm\AiTranslate\Classes\ModelTranslator;

class ModelTranslatorStructureTest extends TranslatorTestCase
{
    public function testMultisiteTrueTranslatesTopLevelAndRepeaterFields()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\Test', [
            'title' => 'Hello',
            'description' => 'A test description',
        ]);

        $item = $source->items()->create(['label' => 'First', 'icon' => 'icon-star']);
        $item->sub_items()->create(['sub_label' => 'Sub One']);
        $source->items()->create(['label' => 'Second', 'icon' => 'icon-bolt']);

        $this->fakeTranslation([
            'title' => 'Hallo',
            'description' => 'Eine Testbeschreibung',
            'items' => [
                ['label' => 'Erste', 'sub_items' => [['sub_label' => 'Unter Eins']]],
                ['label' => 'Zweite'],
            ],
        ]);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');

        $this->assertTrue($result['success']);

        $target = \Site::withContext($this->targetSite->id, function () use ($source) {
            return $source->findForSite($this->targetSite->id);
        });

        $this->assertNotNull($target);
        $this->assertSame('Hallo', $target->title);
        $this->assertSame('Eine Testbeschreibung', $target->description);
        $this->assertSame('Erste', $target->items[0]->label);
        $this->assertSame('icon-star', $target->items[0]->icon, 'non-translatable icon must be copied, not left null');
        $this->assertSame('Unter Eins', $target->items[0]->sub_items[0]->sub_label);
        $this->assertSame('Zweite', $target->items[1]->label);
    }

    public function testEmptyRepeaterItemPreservesPositionalAlignment()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\Test', ['title' => 'Hello']);

        $source->items()->create(['label' => 'First']);
        $source->items()->create(['label' => '', 'icon' => 'icon-bolt']); // no translatable content
        $source->items()->create(['label' => 'Third']);

        $this->fakeTranslation([
            'title' => 'Hallo',
            'items' => [
                ['label' => 'Erste'],
                [], // empty slot, preserved
                ['label' => 'Dritte'],
            ],
        ]);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');
        $this->assertTrue($result['success']);

        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));

        $this->assertSame('Erste', $target->items[0]->label);
        $this->assertSame('', (string) $target->items[1]->label, 'middle item stays untranslated, not shifted');
        $this->assertSame('Dritte', $target->items[2]->label, 'third item must not shift into second position');
    }

    public function testCreateModeSkipsAlreadyTranslatedSite()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\Test', ['title' => 'Hello']);
        \Site::withContext($this->targetSite->id, fn () => $source->findOrCreateForSite($this->targetSite->id));

        $this->fakeTranslation(['title' => 'Should not be used']);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['skipped'] ?? false);
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    public function testUpdateModeOverwritesExistingTranslation()
    {
        $source = $this->createEntry(StructureRecord::class, 'Boarworm\AiTranslate\Test', ['title' => 'Hello']);
        \Site::withContext($this->targetSite->id, function () use ($source) {
            $existing = $source->findOrCreateForSite($this->targetSite->id);
            $existing->update(['title' => 'Stale English Clone']);
        });

        $this->fakeTranslation(['title' => 'Aktualisiert']);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'update');

        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('skipped', $result);

        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));
        $this->assertSame('Aktualisiert', $target->title);
    }
}
