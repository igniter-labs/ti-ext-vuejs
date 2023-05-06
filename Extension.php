<?php

namespace IgniterLabs\VueJs;

use Igniter\Frontend\Components\Contact;
use Main\Classes\MainController;
use System\Classes\BaseComponent;

class Extension extends \System\Classes\BaseExtension
{
    protected const VUE_COMPONENTS_MAP = [
        Contact::class => ['ContactForm'],
    ];

    public function boot()
    {
        MainController::extend(function (MainController $controller) {
            $controller->bindEvent('controller.beforeResponse', function ($url, $page, $output) use ($controller) {
                if (request()->ajax() && $controller->getHandler() === null) {
                    $assets = ['css' => [], 'js' => []]; //$controller->getAssetPaths();
                    $content = $controller->renderPage() ?: '<!-- No content -->';
                    $vueComponents = [];
                    foreach ($page->components as $component) {
                        if ($component->propertyExists('vueComponents') && is_array($component->vueComponents)) {
                            $vueComponents_chunk = $component->vueComponents;
                            foreach ($vueComponents_chunk as $tag => $name) {
                                $tag = is_integer($tag) ? str_slug($name) : $tag;
                                $vueComponents[$tag] = $name;
                            }
                        }
                    }

                    return [
                        'template' => $content,
                        'assets' => $assets,
                        'components' => $vueComponents,
                    ];
                }
            });
        });

        BaseComponent::extend(function (BaseComponent $component) {
            $component->bindEvent('component.run', function () use ($component) {
                if  ($vueComponents = array_get(self::VUE_COMPONENTS_MAP, get_class($component), [])) {
                    $component->addDynamicProperty('vueComponents', $vueComponents);
                    array_map(function ($componentName) use ($component) {
                        $component->addJs(sprintf('components/%s.vue.js', $componentName), 'vue-js');
                    }, $vueComponents);
                }
            });
        });
    }

    public function registerComponents()
    {
        return [
            \IgniterLabs\VueJs\Components\Layout::class => [
                'code' => 'vueLayout',
                'name' => 'Layout Component',
                'description' => 'Default layout for building VueJs powered pages',
            ],
        ];
    }
}
