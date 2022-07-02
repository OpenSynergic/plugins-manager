<?php

namespace OpenSynergic\Plugins;

use Illuminate\Support\Str;

class PluginManager
{
    protected array $plugins = [];

    protected ?string $table;

    public function __construct()
    {
        $this->table(config('plugins-manager.table_names.plugin_settings') ?? 'plugin_settings');
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function table($table): void
    {
        $this->table = $table;
    }

    public function register(Plugin $plugin, string $path): bool
    {
        $pluginName = Str::lower(pathinfo($path, PATHINFO_FILENAME));
        // dont register the same plugin twice
        if (isset($this->plugins[$pluginName])) {
            return false;
        }

        $plugin->register($path, $pluginName);

        $this->plugins[$pluginName] = $plugin;

        return true;
    }

    public function init(Plugin $plugin, bool $onlyEnabled = true): void
    {
        if ($onlyEnabled && !$plugin->isEnabled()) {
            return;
        }

        $plugin->init();
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function getEnabledPlugins(): array
    {
        return collect($this->plugins)
            ->filter(fn (Plugin $plugin) => $plugin->isEnabled())
            ->toArray();
    }

    public function getPlugin(string $class): ?Plugin
    {
        return $this->plugins[$class] ?? null;
    }

    public function getPluginAdminPages(): array
    {
        return collect($this->plugins)
            ->filter(fn (Plugin $plugin) => $plugin->isEnabled())
            ->map(fn (Plugin $plugin) => $plugin->getAdminPages())
            ->flatten()
            ->toArray();
    }

    public function getPluginAdminResources(): array
    {
        return collect($this->plugins)
            ->filter(fn (Plugin $plugin) => $plugin->isEnabled())
            ->map(fn (Plugin $plugin) => $plugin->getAdminResources())
            ->flatten()
            ->toArray();
    }

    public function getPluginAdminWidgets(): array
    {
        return collect($this->plugins)
            ->filter(fn (Plugin $plugin) => $plugin->isEnabled())
            ->map(fn (Plugin $plugin) => $plugin->getAdminWidgets())
            ->flatten()
            ->toArray();
    }

    public function getRows(): array
    {
        return collect($this->getPlugins())
            ->map(fn (Plugin $plugin) => [
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
}
