<?php

namespace Boarworm\AiTranslate\Console;

use Site;
use Illuminate\Console\Command;
use Tailor\Models\EntryRecord;
use Boarworm\AiTranslate\Classes\ModelTranslator;

/**
 * BulkTranslate translates every entry of a Tailor blueprint (structure/stream/entry -
 * EntryRecord::inSection() self-resolves to the correct subclass, so nested-tree
 * structures are handled the same way, no special-casing needed) from a source site
 * into one or more target sites.
 */
class BulkTranslate extends Command
{
    /**
     * @var string signature of the console command
     */
    protected $signature = 'aitranslate:bulk
        {blueprint : Blueprint handle to translate, e.g. Investments\\Category or Builder}
        {--from= : Source site code (default: the site marked default/primary)}
        {--to=* : Target site code(s) to translate into (default: all other sites)}
        {--mode=create : create (skip existing) or update (overwrite existing)}';

    /**
     * @var string description of the console command
     */
    protected $description = 'Translate all entries of a blueprint into other sites using AI Translate';

    public function handle()
    {
        $blueprint = $this->argument('blueprint');
        $mode = $this->option('mode');
        $fromCode = $this->option('from');
        $toCodes = $this->option('to');

        $allSites = Site::listSites();

        $sourceSite = $fromCode
            ? $allSites->firstWhere('code', $fromCode)
            : ($allSites->firstWhere('is_primary', true) ?: $allSites->first());

        if (!$sourceSite) {
            $this->error($fromCode ? "Source site '{$fromCode}' not found." : 'No default site found.');
            return 1;
        }

        $targetSites = $toCodes
            ? $allSites->whereIn('code', $toCodes)
            : $allSites->filter(fn ($site) => $site->id != $sourceSite->id);

        $targetSites = $targetSites->filter(fn ($site) => $site->id != $sourceSite->id)->values();

        if ($targetSites->isEmpty()) {
            $this->error('No matching target site(s) found.');
            return 1;
        }

        $entries = Site::withContext($sourceSite->id, function () use ($blueprint) {
            return EntryRecord::inSection($blueprint)->get();
        });

        if ($entries->isEmpty()) {
            $this->warn("No entries found for blueprint '{$blueprint}'.");
            return 0;
        }

        $this->info("Translating {$entries->count()} '{$blueprint}' entries from '{$sourceSite->name}' into: " . $targetSites->pluck('name')->implode(', '));

        $translator = new ModelTranslator();
        $bar = $this->output->createProgressBar($entries->count() * $targetSites->count());
        $bar->start();

        $successes = 0;
        $skipped = 0;
        $failures = 0;

        foreach ($entries as $entry) {
            foreach ($targetSites as $targetSite) {
                $result = $translator->translateModelToSite($entry, $sourceSite, $targetSite, $mode);

                if (!($result['success'] ?? false)) {
                    $failures++;
                    $this->newLine();
                    $this->error("Entry #{$entry->id} -> {$targetSite->name}: " . ($result['error'] ?? 'unknown error'));
                } elseif ($result['skipped'] ?? false) {
                    $skipped++;
                } else {
                    $successes++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Translated: {$successes}, Skipped (already exists): {$skipped}, Failed: {$failures}.");

        return $failures > 0 ? 1 : 0;
    }
}
