<?php

require_once __DIR__ . '/fixtures/TranslatorTestCase.php';

use Tailor\Models\GlobalRecord;
use Boarworm\AiTranslate\Classes\ModelTranslator;

class ModelTranslatorGlobalTest extends TranslatorTestCase
{
    /**
     * Global blueprint repeaters are deliberately not exercised here: the shared
     * modules/tailor/database/migrations/2021_05_01_000001_Db_Tailor_Globals.php
     * table 'tailor_global_repeaters' has no 'site_root_id' column (unlike the
     * dedicated per-blueprint repeater tables structure/stream get), so a
     * multisite Global with a repeater field throws a core SQL error - a real
     * October core gap, not something fixable from this plugin.
     */
    public function testMultisiteTrueTranslatesTopLevelFields()
    {
        $source = GlobalRecord::findForGlobal('Boarworm\AiTranslate\TestGlobal');
        $source->title = 'Hello';
        $source->description = 'A test description';
        $source->save();

        $this->fakeTranslation([
            'title' => 'Hallo',
            'description' => 'Eine Testbeschreibung',
        ]);

        $result = (new ModelTranslator)->translateModelToSite($source, $this->sourceSite, $this->targetSite, 'create');

        $this->assertTrue($result['success']);

        $target = \Site::withContext($this->targetSite->id, fn () => $source->findForSite($this->targetSite->id));
        $this->assertSame('Hallo', $target->title);
        $this->assertSame('Eine Testbeschreibung', $target->description);
    }
}
