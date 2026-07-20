<?php
namespace Boarworm\AiTranslate\Classes\Event\TailorEntry;

use Event;
use Tailor\Controllers\Entries;
use Tailor\Models\EntryRecord;
use Boarworm\AiTranslate\Models\Setting;

/**
 * Class TailorEntryColumnsHandler
 * @package Boarworm\AiTranslate\Classes\Event\TailorEntry
 */
class TailorEntryColumnsHandler
{
	/**
	 * Add listeners
	 * @param \October\Rain\Events\Dispatcher $event
	 */
	public function subscribe($event)
	{
		Event::listen('backend.list.extendColumns', function ($widget) {
			$this->extendColumns($widget);
		});
	}

	protected function extendColumns($widget)
	{
		if (!($widget->getController() instanceof Entries && $widget->model instanceof EntryRecord)) {
			return;
		}

		$tailorUuids = array_column(Setting::get('tailor_entities', []), 'entity');
		$blueprintUuid = $widget->model->blueprint_uuid ?? null;
		if (!in_array($blueprintUuid, $tailorUuids)) {
			return;
		}

		$widget->addColumns([
			'translation_status' => [
				'label' => 'Translations',
				'type' => 'partial',
				'path' => '$/boarworm/aitranslate/partials/_translation_status.php',
				'sortable' => false,
			]
		]);
	}
}
