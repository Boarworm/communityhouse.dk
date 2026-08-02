<?php

use System\Models\SiteDefinition;

/**
 * TranslatorTestCase provides the common fixture for all AiTranslate tests:
 * two sites (source + target) and the Tailor blueprints under
 * plugins/boarworm/aitranslate/blueprints migrated into the test database.
 */
abstract class TranslatorTestCase extends PluginTestCase
{
    protected $sourceSite;

    protected $targetSite;

    /**
     * fixtureBlueprintsPath is where the test blueprint YAMLs actually live.
     * Tailor only auto-discovers blueprints from each plugin's own
     * blueprints/ directory, so they're copied there for the duration of
     * the test run and removed afterwards - this keeps them out of the
     * backend nav on every other request.
     */
    protected $fixtureBlueprintsPath = __DIR__ . '/blueprints';

    protected $pluginBlueprintsPath = __DIR__ . '/../../blueprints';

    public function setUp(): void
    {
        parent::setUp();

        $this->installFixtureBlueprints();

        SiteDefinition::syncPrimarySite();
        $this->sourceSite = SiteDefinition::first();
        $this->sourceSite->code = 'en';
        $this->sourceSite->locale = 'en';
        $this->sourceSite->save();

        $this->targetSite = new SiteDefinition;
        $this->targetSite->name = 'Deutsch';
        $this->targetSite->code = 'de';
        $this->targetSite->locale = 'de';
        $this->targetSite->is_enabled = true;
        $this->targetSite->is_enabled_edit = true;
        $this->targetSite->group_id = $this->sourceSite->group_id;
        $this->targetSite->save();

        \Tailor\Classes\BlueprintIndexer::instance()->migrate();
    }

    public function tearDown(): void
    {
        $this->removeFixtureBlueprints();

        parent::tearDown();
    }

    protected function installFixtureBlueprints(): void
    {
        if (!is_dir($this->pluginBlueprintsPath)) {
            mkdir($this->pluginBlueprintsPath, 0755, true);
        }

        foreach (glob($this->fixtureBlueprintsPath . '/*.yaml') as $file) {
            copy($file, $this->pluginBlueprintsPath . '/' . basename($file));
        }
    }

    protected function removeFixtureBlueprints(): void
    {
        foreach (glob($this->fixtureBlueprintsPath . '/*.yaml') as $file) {
            $copied = $this->pluginBlueprintsPath . '/' . basename($file);
            if (file_exists($copied)) {
                unlink($copied);
            }
        }

        if (is_dir($this->pluginBlueprintsPath) && count(scandir($this->pluginBlueprintsPath)) === 2) {
            rmdir($this->pluginBlueprintsPath);
        }
    }

    /**
     * createEntry creates a Tailor entry for $handle with a fillable-bypassed
     * 'slug' (Tailor content models don't have 'slug' in $fillable by default -
     * see ModelTranslator::saveTranslation()'s own addFillable(['slug']) call).
     */
    protected function createEntry(string $modelClass, string $handle, array $attributes)
    {
        $attributes += ['slug' => \Str::slug($attributes['title'] ?? \Str::random(8))];

        // inSection() returns a model instance already extended with the blueprint -
        // ->create()/->make() would spawn a *new*, unextended instance internally and
        // lose that, so fill+save directly on the instance inSection() gave us.
        $entry = $modelClass::inSection($handle);
        $entry->addFillable(['slug', 'fullslug']);
        $entry->fill($attributes);
        $entry->save();

        return $entry;
    }

    /**
     * fakeTranslation makes TranslateManager return $responseData without
     * calling a real AI provider - patches the plugin setting to force the
     * Claude provider, then fakes its HTTP endpoint with a canned response.
     */
    protected function fakeTranslation(array $responseData): void
    {
        \Boarworm\AiTranslate\Models\Setting::set([
            'provider' => 'claude',
            'claude_api_key' => 'test-key',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'api.anthropic.com/*' => \Illuminate\Support\Facades\Http::response([
                'stop_reason' => 'end_turn',
                'content' => [
                    ['text' => json_encode($responseData, JSON_UNESCAPED_UNICODE)],
                ],
            ], 200),
        ]);
    }
}
