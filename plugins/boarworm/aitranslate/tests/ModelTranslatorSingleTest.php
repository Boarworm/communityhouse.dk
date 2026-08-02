<?php

require_once __DIR__ . '/fixtures/TranslatorTestCase.php';

use Tailor\Models\SingleRecord;
use Boarworm\AiTranslate\Classes\ModelTranslator;

class ModelTranslatorSingleTest extends TranslatorTestCase
{
    public function testMultisiteTrueTranslatesTopLevelAndRepeaterFields()
    {
        $source = $this->createEntry(SingleRecord::class, 'Boarworm\AiTranslate\TestSingle', [
            'title' => 'Hello',
            'description' => 'A test description',
        ]);

        $source->items()->create(['label' => 'First', 'icon' => 'icon-star']);

        $this->fakeTranslation([
            'title' => 'Hallo',
            'description' => 'Eine Testbeschreibung',
            'items' => [
                ['label' => 'Erste'],
            ],
        ]);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');

        $this->assertTrue($result['success']);

        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));
        $this->assertSame('Hallo', $target->title);
        $this->assertSame('Eine Testbeschreibung', $target->description);
        $this->assertSame('Erste', $target->items[0]->label);
        $this->assertSame('icon-star', $target->items[0]->icon);
    }
}
