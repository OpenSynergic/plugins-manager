<?php

return [
  'path' => base_path(env('PLUGINS_DIR', 'plugins')),

  'cache_duration' => 86400, // 24 hours

  'table_names' => [
    'plugin_settings' => 'plugin_settings',
  ]
];
