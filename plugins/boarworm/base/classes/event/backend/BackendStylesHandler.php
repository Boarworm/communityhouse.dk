<?php
namespace Boarworm\Base\Classes\Event\Backend;

use Event;

/**
 * Class BackendStylesHandler
 * @package Boarworm\Base\Classes\Event\Backend
 */
class BackendStylesHandler
{
	/**
	 * Add listeners
	 * @param \October\Rain\Events\Dispatcher $event
	 */
	public function subscribe($event)
	{
		Event::listen('backend.page.beforeDisplay', function ($controller) {
			$this->addBackendCss($controller);
		});
	}

	protected function addBackendCss($controller)
	{
		$controller->addCss('/plugins/boarworm/base/assets/css/backend.css');
	}
}
