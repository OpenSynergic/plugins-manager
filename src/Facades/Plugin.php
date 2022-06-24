<?php

namespace OpenSynergic\Plugins\Facades;

use Illuminate\Support\Facades\Facade;
use OpenSynergic\Plugins\PluginManager;

/**
 * @see \OpenSynergic\Plugins\PluginManager
 */
class Plugin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PluginManager::class;
    }
}
