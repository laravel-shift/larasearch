<?php

namespace Gtk\Larasearch\Console;

use Illuminate\Console\Command;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larasearch:clear {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the given model from the search index';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function handle(Dispatcher $events)
    {
        $class = $this->argument('model');

        (new $class)::removeAllFromSearch();

        $this->info('All ['.$class.'] records have been cleared.');
    }
}
