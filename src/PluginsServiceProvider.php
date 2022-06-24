<?php

namespace OpenSynergic\Plugins;

use Filament\PluginServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Spatie\LaravelPackageTools\Package;
use OpenSynergic\Plugins\Commands\PluginsCommand;
use OpenSynergic\Plugins\Facades\Plugin as PluginFacade;
use OpenSynergic\Plugins\Filament\Pages\Plugins;

class PluginsServiceProvider extends PluginServiceProvider
{
    public static string $name = 'plugins-manager';

    protected array $pages = [
        Plugins::class,
    ];

    public function configurePackage(Package $package): void
    {
        parent::configurePackage($package);
    }

    public function packageConfigured(Package $package): void
    {
        $package
            ->hasMigration('create_plugin_settings_table')
            ->hasCommand(PluginsCommand::class);
    }

    public function packageRegistered(): void
    {
        parent::packageRegistered();

        $this->app->beforeResolving('filament', function () {
            $this->app->make(PluginManager::class);
        });

        $this->app->resolving(PluginManager::class, function (): void {
            $this->discoverPlugins();
        });

        $this->app->scoped(PluginManager::class, function (): PluginManager {
            return new PluginManager();
        });
    }

    public function packageBooted(): void
    {
        parent::packageBooted();
    }

    public function discoverPlugins(): void
    {
        $filesystem = app(Filesystem::class);
        $filesystem->ensureDirectoryExists(config('plugins-manager.path'));
        collect($filesystem->directories(config('plugins-manager.path')))
            ->filter(fn ($path) => $filesystem->exists($path . '/index.php'))
            ->mapWithKeys(fn ($path) => [$path =>  $filesystem->requireOnce($path . '/index.php')])
            ->filter(fn ($class) => is_subclass_of($class, Plugin::class))
            ->each(function (Plugin $plugin, $path) use ($filesystem) {
                $register = PluginFacade::register($plugin, $path);
                // initialize plugin and register resources
                if ($register) {
                    PluginFacade::init($plugin);

                    $this->loadViewsFrom($plugin->getPluginPath() . '/resources/views', $plugin->getPluginName());

                    if ($filesystem->exists($configFile = $plugin->getPluginPath() . '/config/config.php')) {
                        $this->mergeConfigFrom($configFile, $plugin->getPluginName());
                    }
                }
            });
    }

    protected function getPages(): array
    {
        return array_merge($this->pages, PluginFacade::getPluginAdminPages());
    }

    protected function getResources(): array
    {
        return array_merge($this->resources, PluginFacade::getPluginAdminResources());
    }
}
