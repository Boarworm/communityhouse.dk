<?php

namespace Boarworm\AiTranslate\Classes;

/**
 * ModelTranslator carries the model-agnostic translation logic (used by both the
 * AiTranslate form widget and the aitranslate:bulk console command) so a record
 * can be translated without depending on a backend Form widget / controller context.
 */
class ModelTranslator
{
    protected $textFieldTypes = ['text', 'textarea', 'richeditor', 'markdown', 'codeeditor'];

    /**
     * Translate $sourceModel (as it exists on $sourceSite) into $targetSite.
     * Returns the same result shape used by the AiTranslate form widget's AJAX handler.
     */
    public function translateModelToSite($sourceModel, $sourceSite, $targetSite, string $mode = 'create'): array
    {
        try {
            if ($mode === 'create' && $sourceModel->findForSite($targetSite->id)) {
                return ['success' => true, 'skipped' => true, 'site' => $targetSite->name];
            }

            $dataToTranslate = $this->prepareDataToTranslate($sourceModel);

            if (empty($dataToTranslate)) {
                return ['success' => false, 'error' => 'No translatable fields found', 'site' => $targetSite->name];
            }

            $manager = new TranslateManager();
            $sourceLang = $sourceSite->locale ?? $sourceSite->name;
            $targetLang = $targetSite->locale ?? $targetSite->name;

            $translatedData = $manager->translate($dataToTranslate, $sourceLang, $targetLang);

            if (empty($translatedData)) {
                return ['success' => false, 'error' => 'Translation returned no data', 'site' => $targetSite->name];
            }

            \Site::withContext($targetSite->id, function () use ($sourceModel, $mode, $translatedData, $targetSite) {
                $targetModel = $this->duplicateForSite($sourceModel, $targetSite->id, $mode);
                $this->saveTranslation($targetModel, $translatedData);
            });

            return ['success' => true, 'site' => $targetSite->name];
        } catch (\Throwable $ex) {
            trace_log("AI Translate CRITICAL ERROR for site {$targetSite->name}: " . $ex->getMessage() . " in " . $ex->getFile() . " on line " . $ex->getLine());
            trace_log($ex->getTraceAsString());
            return ['success' => false, 'error' => $ex->getMessage(), 'site' => $targetSite->name];
        }
    }

    /**
     * Prepare data to translate, reading field definitions straight from the
     * blueprint fieldset so this works with or without a backend Form widget.
     * Backend\Widgets\Form\FieldProcessor::processUntranslatableWidgets() force-clears
     * translatable on repeater/nestedform widgets on a backend-processed FormField,
     * so we deliberately do not use $this->getParentForm()->getFields() here - this
     * also means repeater fields no longer need a special-cased bypass.
     */
    public function prepareDataToTranslate($model): array
    {
        $dataToTranslate = [];

        // Fields that are always translatable if exist
        $alwaysTranslatable = ['title', 'slug', 'fullslug'];
        foreach ($alwaysTranslatable as $field) {
            if (isset($model->{$field})) {
                $dataToTranslate[$field] = $model->{$field};
            }
        }

        foreach ($model->getFieldsetDefinition()->getAllFields() as $name => $field) {
            $fieldType = $field->config['type'] ?? null;

            if ($fieldType === 'repeater') {
                $repeaterData = $this->extractRepeaterTranslatableData($model->{$name}, []);
                if (!empty($repeaterData)) {
                    $dataToTranslate[$name] = $repeaterData;
                }
                continue;
            }

            if (!($field->config['translatable'] ?? false)) {
                continue;
            }

            if (in_array($fieldType, $this->textFieldTypes)) {
                $dataToTranslate[$name] = $model->{$name};
            }
        }

        return $dataToTranslate;
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

        // Collection keys are the RepeaterItem's own DB id, not its position - and the
        // target model (a fresh duplicate) will have entirely different ids. So we key
        // $result by iteration POSITION (0, 1, 2...) instead, and never drop an empty
        // item's slot, so later siblings don't shift when applied against $items->values()
        // in updateRepeaterTranslations().
        $position = 0;

        foreach ($items as $item) {
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

            // Always keep the slot (even empty) to preserve positional alignment.
            $result[$position] = $itemData;
            $position++;
        }

        return $result;
    }

    /**
     * Get translatable field keys from a RepeaterItem using its blueprint fieldset.
     * getFieldsetDefinition() is bound dynamically (October Extendable), so a direct
     * $item->getFieldsetDefinition() call throws "Call to undefined method" even
     * though method_exists() reports true - it must be invoked via reflection.
     */
    protected function getTranslatableFieldKeys($item): array
    {
        $keys = [];
        try {
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
        // $model->{$attribute} is keyed by the RepeaterItem's own DB id (not position),
        // and this is a freshly duplicated target with different ids entirely - so
        // normalize to plain sequential position via values(), matching the positional
        // slots assigned in extractRepeaterTranslatableData().
        $items = $model->{$attribute}->values();

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

        if ($model->isMultisiteSyncEnabled()) {
            if ($existing) {
                $existing->addFillable(['slug', 'fullslug']);
                $existing->update($model->getAttributes());
            }
        } else {
            if ($existing && $mode === 'update') {
                $existing->forceDelete();
            }
        }

        return $model->findOrCreateForSite($siteId);
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
