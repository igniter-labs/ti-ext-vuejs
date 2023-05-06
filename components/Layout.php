<?php

namespace IgniterLabs\VueJs\Components;

use Main\Template\Page;
use Main\Traits\UsesPage;
use System\Classes\BaseComponent;

class Layout extends BaseComponent
{
    use UsesPage;

    public function defineProperties()
    {
        return [
            'rootElementId' => [
                'label' => 'Root Vue App element (el)',
                'type' => 'text',
                'default' => '#app',
                'validationRule' => 'required|string',
            ],
            'enableRouting' => [
                'label' => 'Enable Vue Router',
                'type' => 'switch',
                'default' => true,
                'validationRule' => 'required|boolean',
            ],
        ];
    }

    public function onRun()
    {
        $this->addJs('https://unpkg.com/vue@3', 'vue');
        $this->addJs('https://unpkg.com/vue-router@4', 'vue-router');

        $this->page['vueThemePages'] = Page::listInTheme($this->controller->getTheme());
        $this->page['vueAppSelector'] = $this->property('rootElementId');
        $this->page['vueWithRouter'] = $this->property('enableRouting');
        $this->page['baseUrl'] = request()->path();

        $page = $this->controller->getPage();
        $vueComponents = [];
        foreach ($page->components as $component) {
            if (
                $component->propertyExists('vueComponents')
                && is_array($component->vueComponents)
            ) {
                foreach ($component->vueComponents as $tag => $name) {
                    $tag = is_integer($tag) ? str_slug($name) : $tag;
                    $vueComponents[$tag] = $name;
                }
            }
        }

        $this->page['vueComponents'] = $vueComponents;
    }

    public static function getThemePageOptions()
    {
        if (self::$themePageOptionsCache)
            return self::$themePageOptionsCache;

        return self::$themePageOptionsCache = Page::getDropdownOptions();
    }
}