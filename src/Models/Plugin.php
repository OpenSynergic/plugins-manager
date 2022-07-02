<?php

namespace OpenSynergic\Plugins\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use OpenSynergic\Plugins\Facades\Plugin as FacadesPlugin;
use Sushi\Sushi;

class Plugin extends Model
{
    use Sushi;

    public function getRows()
    {
        return collect(FacadesPlugin::getPlugins())
            ->map(fn ($plugin) => [
                'name' => $plugin->getName(),
                'pluginName' => $plugin->getPluginName(),
                'description' => $plugin->getDescription(),
                'path' => $plugin->getPluginPath(),
                'class' => $plugin::class,
                'enabled' => $plugin->isEnabled(),
            ])
            ->values()
            ->toArray();
    }

    public function enabled(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => FacadesPlugin::getPlugin($this->pluginName)->isEnabled(),
            set: fn ($value) => FacadesPlugin::getPlugin($this->pluginName)->setEnabled($value)
        );
    }

    public function getPlugin()
    {
        return FacadesPlugin::getPlugin($this->pluginName);
    }
}
