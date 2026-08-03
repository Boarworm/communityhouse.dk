<?php namespace Boarworm\Base\Updates\Seeders;

use Site;
use Seeder;
use Tailor\Models\EntryRecord;

/**
 * SeedPageMeta sets SEO meta title/description for the primary (English) site's
 * Site\Pages\* entries, replacing the generic placeholder copy from the starter theme.
 */
class SeedPageMeta extends Seeder
{
    /**
     * @var array meta keyed by Site\Pages\* blueprint handle
     */
    protected $meta = [
        'Site\Pages\Home' => [
            'meta_title' => 'Free support for migrant workers in Copenhagen',
            'meta_description' => "Community House Copenhagen offers free counseling, English classes, and a weekly support group for migrant workers navigating life and work in Denmark.",
        ],
        'Site\Pages\About' => [
            'meta_title' => 'About us',
            'meta_description' => "We're a volunteer-run, donation-funded group supporting migrant workers in Copenhagen with free counseling, English classes, and a support group.",
        ],
        'Site\Pages\Contact' => [
            'meta_title' => 'Contact us',
            'meta_description' => "Get in touch with Community House Copenhagen, near Copenhagen's Town Hall Square. We reply quickly and you're always welcome to stop by in person.",
        ],
        'Site\Pages\Activities' => [
            'title' => 'Activities',
            'slug' => 'activities',
            'meta_title' => 'Weekly activities',
            'meta_description' => "Free weekly activities at Community House Copenhagen — counseling, English classes, and a support group for migrant workers in Denmark.",
        ],
        'Site\Pages\Calendar' => [
            'title' => 'Calendar',
            'slug' => 'calendar',
            'meta_title' => 'Calendar',
            'meta_description' => "Upcoming events and activity dates at Community House Copenhagen.",
        ],
        'Site\Pages\FolkPaaVej' => [
            'title' => 'Folk på Vej',
            'slug' => 'folk-paa-vej',
            'meta_title' => 'Folk på Vej',
            'meta_description' => "Folk på Vej, a project by Community House Copenhagen.",
        ],
    ];

    /**
     * run the database seeds.
     */
    public function run()
    {
        $primarySiteId = Site::getPrimarySite()->id;

        Site::applyActiveSiteId($primarySiteId);

        foreach ($this->meta as $handle => $attributes) {
            $record = EntryRecord::inSection($handle)->first();

            if (!$record) {
                $record = EntryRecord::inSection($handle);
                $record->title = $attributes['title'];
                $record->slug = $attributes['slug'];
                $record->is_enabled = 1;
            }

            $record->meta_title = $attributes['meta_title'];
            $record->meta_description = $attributes['meta_description'];
            $record->save();

            $this->line("* Updated meta for [{$handle}]");
        }
    }
}
