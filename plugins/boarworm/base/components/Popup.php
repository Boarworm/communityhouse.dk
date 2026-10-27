<?php namespace Boarworm\Base\Components;

use Cms\Classes\ComponentBase;
use Tailor\Models\EntryRecord;

/**
 * Popup Component
 *
 * @link https://docs.octobercms.com/4.x/extend/cms-components.html
 */
class Popup extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Popup Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * @link https://docs.octobercms.com/4.x/element/inspector-types.html
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * onShowPopup renders the popup content partial for the requested type
     * and the record it needs.
     */
    public function onShowPopup()
    {
        $this->page['popupContent'] = [
            'type' => post('dataType'),
            'data' => $this->loadPopupData(post('dataType')),
        ];

        return [
            '.js-popup-content' => $this->renderPartial('blocks/popup/popup-content'),
        ];
    }

    /**
     * loadPopupData returns the record shown by the given popup type.
     */
    protected function loadPopupData(?string $type): ?EntryRecord
    {
        if ($type === 'magazineEdition') {
            return EntryRecord::inSection('Magazine\Edition')->applyPublishedStatus()->find((int) post('id'));
        }

        return null;
    }
}
