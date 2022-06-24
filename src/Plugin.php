<?php

namespace OpenSynergic\Plugins;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Traits\Macroable;
use OpenSynergic\Plugins\Facades\Plugin as FacadesPlugin;

abstract class Plugin
{
  use Macroable;

  protected string $pluginPath;
  protected string $pluginName;
  protected array $pluginTags = [];
  protected array $adminPages = [];
  protected array $adminResources = [];
  protected array $adminWidgets = [];

  abstract function init(): void;

  abstract function getName(): string;

  abstract function getDescription(): string;

  public function getPluginTags(): array
  {
    return $this->pluginTags;
  }

  public function register($path, $pluginName)
  {
    $this->pluginPath = $path;
    $this->pluginName = $pluginName;
  }

  function getPluginPath()
  {
    return $this->pluginPath;
  }

  function getPluginName()
  {
    return $this->pluginName;
  }

  function getAdminPages()
  {
    return $this->adminPages;
  }

  function getAdminResources()
  {
    return $this->adminResources;
  }

  function getAdminWidgets()
  {
    return $this->adminWidgets;
  }

  function getSetting($name)
  {
    return Cache::remember(static::class . $name, config('plugins-manager.cache_duration') ?? 86400, function () use ($name) {
      return DB::table(FacadesPlugin::getTable())
        ->whereName(static::class)
        ->whereSettingName($name)
        ->value('setting_value');
    });
  }

  function setSetting($name, $value)
  {
    Cache::forget(static::class . $name);

    return DB::table(FacadesPlugin::getTable())
      ->updateOrInsert([
        'name' => static::class,
        'setting_name' => $name,
      ], [
        'name' => static::class,
        'setting_name' => $name,
        'setting_value' => $value,
      ]);
  }

  function isEnabled(): bool
  {
    return $this->getSetting('enabled') ? true : false;
  }

  function isDisabled(): bool
  {
    return !$this->isEnabled();
  }

  function setEnabled(bool $enabled): void
  {
    $this->setSetting('enabled', $enabled ? '1' : '0');
  }

  function toggleEnabled()
  {
    $this->setEnabled(!$this->isEnabled());
  }
}
