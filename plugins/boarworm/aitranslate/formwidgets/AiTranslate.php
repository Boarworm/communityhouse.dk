<?php

namespace Boarworm\AiTranslate\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Backend\Classes\FormField;
use Boarworm\AiTranslate\Classes\TranslateManager;

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

    public function save()
    {
        $this->controller->asExtension('FormController')->update_onSave($this->model->id);
        \Flash::forget();
    }

    /**
     * Save form once before translation loop
     */
    public function onSaveForm()
    {
        $this->save();
    }

    public function onTranslateSingle()
    {
        try {
            $targetSiteId = post('site_id');
            $sourceModel = $this->model;
           
            $mode = post('mode', 'create');
            $targetSiteId = post('site_id');

            if (!$targetSiteId) {
                return ['success' => false, 'error' => 'No site ID provided'];
            }

            $currentSite = \Site::getEditSite();
            $targetSite = \Site::listSites()->firstWhere('id', $targetSiteId);

            if (!$targetSite || $targetSite->id == $currentSite->id) {
                return ['success' => false, 'error' => 'Invalid target site'];
            }

            if ($mode === 'create' && $sourceModel->findForSite($targetSite->id)) {
                return ['success' => true, 'skipped' => true, 'site' => $targetSite->name];
            }
            
            $dataToTranslate = $this->prepareDataToTranslate($sourceModel);

            if (empty($dataToTranslate)) {
                return ['success' => false, 'error' => 'No translatable fields found'];
            }

            $manager = new TranslateManager();
            $sourceLang = $currentSite->locale ?? $currentSite->name;
            $targetLang = $targetSite->locale ?? $targetSite->name;

            $translatedData = $manager->translate($dataToTranslate, $sourceLang, $targetLang);

            if (!empty($translatedData)) {
                \Site::withContext($targetSite->id, function() use ($sourceModel, $mode, $translatedData, $targetSite) {
                    $targetModel = $this->duplicateForSite($sourceModel, $targetSite->id, $mode);
                    $this->saveTranslation($targetModel, $translatedData);
                });
            }

            return ['success' => true, 'site' => $targetSite->name];

        } catch (\Throwable $ex) {
            $siteName = isset($targetSite) ? $targetSite->name : 'unknown';
            trace_log("AI Translate CRITICAL ERROR for site {$siteName}: " . $ex->getMessage() . " in " . $ex->getFile() . " on line " . $ex->getLine());
            trace_log($ex->getTraceAsString());
            return ['success' => false, 'error' => $ex->getMessage(), 'site' => $siteName];
        }
    }

    /**
     * Extract translatable data from repeater items using blueprint definition or reflection
     */
    protected function extractRepeaterTranslatableData($items, array $blueprintConfig = []): array
    {
        if (!$items || (is_object($items) && $items->isEmpty())) {
            return [];
        }

        $translatableKeys = [];
        $nestedRepeaterConfigs = [];

        // STRICT MODE: Use Blueprint Config
        if (!empty($blueprintConfig)) {
            $itemFieldsConfig = $blueprintConfig['form']['fields'] ?? [];
            foreach ($itemFieldsConfig as $fieldName => $fieldConfig) {
                if (($fieldConfig['translatable'] ?? false) === true) {
                    $translatableKeys[] = $fieldName;
                }
                if (($fieldConfig['type'] ?? '') === 'repeater' || isset($fieldConfig['form']['fields'])) {
                    $nestedRepeaterConfigs[$fieldName] = $fieldConfig;
                }
            }
        }

        $result = [];

        foreach ($items as $index => $item) {
            $itemData = [];
            $itemKeys = $translatableKeys;
            
            // FALLBACK: If no strict keys found (Standard Mode), use Reflection/Fieldset
            if (empty($itemKeys)) {
                $itemKeys = $this->getTranslatableFieldKeys($item);
            }

            // Extract Values
            foreach ($itemKeys as $key) {
                $value = $item->{$key} ?? null;
                // Only include scalar values or simple arrays, skip objects/collections (like repeaters)
                if ($value !== null && $value !== '' && !is_object($value)) {
                    $itemData[$key] = $value;
                }
            }

            // Recurse into nested repeater relations
            foreach ($item->hasMany as $relName => $relConfig) {
                if (($relConfig[0] ?? null) !== 'Tailor\Models\RepeaterItem') {
                    continue;
                }
                
                $nestedConfig = $nestedRepeaterConfigs[$relName] ?? [];
                $nestedItems = $item->{$relName};
                
                if ($nestedItems && !$nestedItems->isEmpty()) {
                    $nestedData = $this->extractRepeaterTranslatableData($nestedItems, $nestedConfig);
                    if (!empty($nestedData)) {
                        $itemData[$relName] = $nestedData;
                    }
                }
            }

            if (!empty($itemData)) {
                $result[$index] = $itemData;
            }
        }

        return $result;
    }

    /**
     * Get translatable field keys from a RepeaterItem using reflection/fieldset
     */
    protected function getTranslatableFieldKeys($item): array
    {
        $keys = [];
        try {
            // Helper to get definition
            if (method_exists($item, 'getFieldsetDefinition')) {
                $ref = new \ReflectionMethod($item, 'getFieldsetDefinition');
                $ref->setAccessible(true);
                $fieldset = $ref->invoke($item);

                if ($fieldset && method_exists($fieldset, 'getAllFields')) {
                    foreach ($fieldset->getAllFields() as $fieldName => $fieldObj) {
                        if (isset($fieldObj->config['translatable']) && $fieldObj->config['translatable']) {
                            $keys[] = $fieldName;
                        }
                    }
                }
            }
        } catch (\Throwable $e) { /* silent */ }
        return $keys;
    }

    /**
     * Update repeater items with translated values (items already copied by findOrCreateForSite)
     */
    protected function updateRepeaterTranslations($model, $attribute, $data)
    {
        $items = $model->{$attribute};

        // Important: Reset keys to 0, 1, 2... to match the collection's sequential order
        // because keys from extractRepeaterTranslatableData are source record IDs.
        $data = array_values($data);

        foreach ($data as $index => $row) {
            // Get target item by position
            if (!$item = $items->get($index)) continue;

            $fill = [];
            $nested = [];

            foreach ($row as $key => $value) {
                // Prevent updating primary/foreign keys
                if (in_array($key, ['id', 'site_id', 'site_root_id', 'parent_id', 'nest_left', 'nest_right', 'nest_depth'])) {
                    continue;
                }

                if (is_array($value)) {
                    $nested[$key] = $value;
                } else {
                    $fill[$key] = $value;
                }
            }

            if ($fill) {
                $item->forceFill($fill);
                $item->forceSave();
            }

            foreach ($nested as $key => $value) {
                $this->updateRepeaterTranslations($item, $key, $value);
            }
        }
    }

    /**
     * Duplicate model for site
     */
    protected function duplicateForSite($model, int $siteId, string $mode)
    {
        $existing = $model->findForSite($siteId);

        if($model->isMultisiteSyncEnabled()){
            // todo: repeater escaped but probably should not be
            if ($existing) {
                $existing->addFillable(['slug', 'fullslug']);
                $existing->update($model->getAttributes());
            }
 
        }else{
            if($existing && $mode === 'update'){
                $existing->forceDelete();
            }
        }

        $otherModel = $model->findOrCreateForSite($siteId);
        return $otherModel;
    }

    /**
     * Prepare data to translate
     */
    protected function prepareDataToTranslate($model): array
    {
        $dataToTranslate = [];
        $textFieldTypes = ['text', 'textarea', 'richeditor', 'markdown', 'codeeditor'];

        // Fields that are always translatable if exist
        $alwaysTranslatable = ['title', 'slug', 'fullslug'];
        foreach ($alwaysTranslatable as $field) {
            if (isset($model->{$field})) {
                $dataToTranslate[$field] = $model->{$field};
            }
        }

        // Get translatable fields
        foreach ($this->getParentForm()->getFields() as $name => $field) {
            if (!($field->config['translatable'] ?? false)) continue;
                
            $fieldType = $field->config['widget'] ?? $field->config['type'] ?? null;

            if(in_array($fieldType, $textFieldTypes)){
                $dataToTranslate[$name] = $model->{$name};
            } else {
                if ($fieldType === 'repeater') {
                    $repeaterData = $this->extractRepeaterTranslatableData($model->{$name}, []);
                    if (!empty($repeaterData)) {
                        $dataToTranslate[$name] = $repeaterData;
                    }
                }
            }
        }

        return $dataToTranslate;
    }

    /**
     * Apply translated data to target model
     */
    protected function saveTranslation($targetModel, array $translatedData): void
    {
        $fillData = [];
        $repeaterData = [];

        foreach ($translatedData as $key => $value) {
            if (is_array($value)) {
                $repeaterData[$key] = $value;
            } else {
                $fillData[$key] = $value;
            }
        }

        $targetModel->addFillable(['slug', 'fullslug']);
        $targetModel->update($fillData);
    
        // Update repeaters
        foreach ($repeaterData as $relName => $items) {
            $this->updateRepeaterTranslations($targetModel, $relName, $items);
        }
    }
}
