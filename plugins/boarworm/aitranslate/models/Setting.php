<?php
namespace Boarworm\AiTranslate\Models;

/**
 * Setting Model
 *
 * @link https://docs.octobercms.com/4.x/extend/system/models.html
 */
class Setting extends \System\Models\SettingModel
{
    use \October\Rain\Database\Traits\Validation;

    public $settingsCode = 'boarworm_aitranslate_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'claude_api_key' => 'required_if:provider,claude',
        'chatgpt_api_key' => 'required_if:provider,chatgpt',
    ];

    public $jsonable = [
        'translate_entities'
    ];

    public function getDataTableOptions()
    {
        $indexer = \Tailor\Classes\BlueprintIndexer::instance();
        $options = [];
        foreach ($indexer->listSections() as $blueprint) {
            $options[$blueprint->uuid] = $blueprint->name . ' (' . $blueprint->handle . ')';
        }
        foreach ($indexer->listGlobals() as $blueprint) {
            $options[$blueprint->uuid] = $blueprint->name . ' (' . $blueprint->handle . ')';
        }
        return $options;
    }
}
