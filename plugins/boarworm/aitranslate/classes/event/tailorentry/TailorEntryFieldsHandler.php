<?php
namespace Boarworm\AiTranslate\Classes\Event\TailorEntry;

use Event;
use Tailor\Controllers\Entries;
use Tailor\Models\EntryRecord;
use Boarworm\AiTranslate\Models\Setting;

/**
 * Class TailorEntryFieldsHandler
 * @package Boarworm\AiTranslate\Classes\Event\TailorEntry
 */
class TailorEntryFieldsHandler
{
	/**
	 * Add listeners
	 * @param \October\Rain\Events\Dispatcher $event
	 */
	public function subscribe($event)
	{
		Event::listen('backend.form.extendFields', function ($widget) {
			$this->extendFields($widget);
		});
	}

	protected function extendFields($widget)
	{
		if (!($widget->getController() instanceof Entries && $widget->model instanceof EntryRecord))
			return;

		$tailorUuids = array_column(Setting::get('tailor_entities', []), 'entity');
		$blueprintUuid = $widget->model->blueprint_uuid ?? null;
		if (!in_array($blueprintUuid, $tailorUuids)) {
			return;
		}

		if (!$widget->isNested) {
			$widget->addTabFields([
				'_translation_status' => [
					'label' => 'Translation Status',
					'tab' => 'AI Translate',
					'type' => 'partial',
					'path' => '$/boarworm/aitranslate/partials/_translation_status.php',
					'translatable' => false,
				],
				'saveandtranslate' => [
					'tab' => 'AI Translate',
					'type' => 'aitranslate',
					'translatable' => false,
				],
			]);
		}
	}
}
