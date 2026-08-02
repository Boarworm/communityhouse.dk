<?php

namespace Boarworm\AiTranslate\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Backend\Classes\FormField;
use Boarworm\AiTranslate\Classes\ModelTranslator;

class AiTranslate extends FormWidgetBase
{
    protected $defaultAlias = 'aitranslate';

    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('aitranslate');
    }

    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;

        $currentSite = \Site::getEditSite();
        $this->vars['targetSites'] = \Site::listSites()->filter(function ($site) use ($currentSite) {
            return $site->id != $currentSite->id;
        });
    }

    public function getSaveValue($value)
    {
        return FormField::NO_SAVE_DATA;
    }

    /**
     * Save form once before translation loop. Saves the model directly (no
     * 'propagate' option) rather than going through the full FormController
     * update_onSave() flow - since October 4.3, a propagate-save on a
     * multisite:sync model auto-clones missing nested-tree children to other
     * sites (Multisite::propagateHasManyRelation()), which then makes the
     * create-mode "already exists" check below skip translating them.
     */
    public function onSaveForm()
    {
        $this->model->save();
        \Flash::forget();
    }

    public function onTranslateSingle()
    {
        $targetSiteId = post('site_id');
        $mode = post('mode', 'create');

        if (!$targetSiteId) {
            return ['success' => false, 'error' => 'No site ID provided'];
        }

        $currentSite = \Site::getEditSite();
        $targetSite = \Site::listSites()->firstWhere('id', $targetSiteId);

        if (!$targetSite || $targetSite->id == $currentSite->id) {
            return ['success' => false, 'error' => 'Invalid target site'];
        }

        return (new ModelTranslator())->translateModelToSite($this->model, $currentSite, $targetSite, $mode);
    }
}
