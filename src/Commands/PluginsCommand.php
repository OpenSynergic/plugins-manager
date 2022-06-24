<?php

namespace OpenSynergic\Plugins\Commands;

use Illuminate\Console\Command;

class PluginsCommand extends Command
{
    public $signature = 'plugins';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
