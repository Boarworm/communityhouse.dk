<?php
namespace Boarworm\AiTranslate;

use Event;
use System\Classes\PluginBase;
use Boarworm\AiTranslate\Models\Setting;
use Boarworm\AiTranslate\Classes\Event\TailorEntry\TailorEntryFieldsHandler;
use Boarworm\AiTranslate\Classes\Event\TailorEntry\TailorEntryColumnsHandler;
use Boarworm\AiTranslate\Classes\Event\TailorGlobal\TailorGlobalFieldsHandler;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/4.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'AI Translate',
            'description' => 'AI-powered translation for October CMS',
            'author' => 'Boarworm',
            'icon' => 'icon-language'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        $this->discoverConsoleCommands();
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        $this->addEventListeners();
    }

    protected function addEventListeners()
    {
        Event::subscribe(TailorEntryFieldsHandler::class);
        Event::subscribe(TailorEntryColumnsHandler::class);
        Event::subscribe(TailorGlobalFieldsHandler::class);
    }

    /**
     * registerFormWidgets registered by this plugin.
     */
    public function registerFormWidgets()
    {
        return [
            'Boarworm\AiTranslate\FormWidgets\AiTranslate' => 'aitranslate'
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return [
            'boarworm.aitranslate.manage_settings' => [
                'tab' => 'AiTranslate',
                'label' => 'Manage settings'
            ],
        ];
    }

    /**
     * registerSettings registered by this plugin.
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'AI Translate',
                'description' => 'Manage API keys and settings.',
                'category' => 'CATEGORY_CMS',
                'icon' => 'icon-language',
                'class' => Setting::class,
                'order' => 500,
                'keywords' => 'translate ai openai claude',
                'permissions' => ['boarworm.aitranslate.manage_settings']
            ]
        ];
    }
}
