<?php

return [
  'title' => 'Plugins',

  'navigationGroup' => 'Plugins',

  'toggle_plugin' => ':pluginName is now :status',

  'success' => [
    'install' => 'Plugin installed successfully',
    'uninstall' => 'Plugin uninstalled successfully'
  ],

  'exceptions' => [
    'no_index_file' => 'The plugin does not contain an index.php file.',
    'path_not_correct' => 'The plugin is not installed in the correct path.',
    'plugin_not_uninstalled' => 'The plugin could not be uninstalled.',
  ]
];
