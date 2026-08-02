<?php
namespace Boarworm\Base\Classes\Event\Site;

use App;
use Cms;
use Event;
use Site;
use Tailor\Models\GlobalRecord;

/**
 * Class SiteAwareVarsHandler
 * @package Boarworm\Base\Classes\Event\Site
 */
class SiteAwareVarsHandler
{
	/**
	 * Add listeners
	 * @param \October\Rain\Events\Dispatcher $event
	 */
	public function subscribe($event)
	{
		$this->shareSiteAwareVars();

		// Plugin::boot() runs before October resolves which site is active for
		// a request (that happens later, in routing middleware) - so sharing
		// these once at boot can capture the wrong (default/primary) site's
		// URL on a multisite request. Re-running on this event makes them
		// reflect the site that actually ends up active.
		Event::listen('system.site.setActiveSite', function () {
			$this->shareSiteAwareVars();
		});
	}

	protected function shareSiteAwareVars()
	{
		\View::share([
			// Distinct from site_link so a future CDN/asset domain only
			// needs to change this one value.
			'asset_base' => Cms::fullUrl('/'),
			'site_link' => Cms::fullUrl('/'),
			'privacy_link' => Cms::fullUrl('/privacy-policy'),
			'terms_link' => Cms::fullUrl('/terms-and-conditions'),
			// Read from the Site\Config Tailor global so mail templates can
			// reference the real site name instead of a hardcoded string.
			// It's `multisite: sync` (shared across sites), so it doesn't
			// need to be re-read per active site the way the URLs above do.
			'site_name' => GlobalRecord::findForGlobal('Site\Config')->site_name ?? '',
		]);

		// Belt-and-braces: October already calls App::setLocale() itself for
		// real frontend requests (see HasActiveSite::applyActiveSite()). This
		// only matters for contexts where no HTTP request applies a site at
		// all - e.g. a mail job processed later by `queue:work`, outside the
		// normal request lifecycle.
		if (($site = Site::getSiteFromContext()) && $site->locale) {
			App::setLocale($site->locale);
		}
	}
}
