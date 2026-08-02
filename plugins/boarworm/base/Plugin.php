<?php namespace Boarworm\Base;

use Backend;
use Event;
use System\Classes\PluginBase;
use Boarworm\Base\Classes\Event\Site\SiteAwareVarsHandler;
use Boarworm\Base\Classes\Event\Backend\BackendStylesHandler;

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
            'name' => 'Base',
            'description' => 'No description provided yet...',
            'author' => 'Boarworm',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        //
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        $this->addEventListeners();
    }

    /**
     * addEventListeners registers this plugin's event subscribers.
     */
    protected function addEventListeners()
    {
        Event::subscribe(SiteAwareVarsHandler::class);
        Event::subscribe(BackendStylesHandler::class);
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Boarworm\Base\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'boarworm.base.some_permission' => [
                'tab' => 'Base',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'base' => [
                'label' => 'Base',
                'url' => Backend::url('boarworm/base/mycontroller'),
                'icon' => 'icon-leaf',
                'permissions' => ['boarworm.base.*'],
                'order' => 500,
            ],
        ];
    }

    public function registerMailLayouts()
    {
        return [
            'main' => 'boarworm.base::layouts.main',
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'base:contact_admin' => 'boarworm.base::mail.contact-admin',
            'base:contact_client' => 'boarworm.base::mail.contact-client',
        ];
    }

    public function registerMailPartials()
    {
        return [
            'header' => 'boarworm.base::partials.header',
            'footer' => 'boarworm.base::partials.footer',
            'button' => 'boarworm.base::partials.button',
        ];
    }
}
