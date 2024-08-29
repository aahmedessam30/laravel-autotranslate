<?php

namespace Ahmedessam\LaravelAutotranslate\Console\Commands;

use Ahmedessam\LaravelAutotranslate\Facades\AutoTranslate;
use Illuminate\Console\Command;

class SyncTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:translations {--lang= : the language to sync the translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the translations from the project files to the language files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $lang = $this->option('lang');

            $this->components->info('Syncing translations...');

            if (!file_exists(resource_path("lang")) && !file_exists(lang_path())) {
                $this->components->warn('The lang directory does not exist, we will create it for you.');
                $this->call('lang:publish');
            }

            if (!file_exists(config_path('autotranslate.php'))) {
                $this->components->warn('The config file does not exist, we will publish it for you.');
                $this->call('vendor:publish', ['--tag' => 'autotranslate-config']);
            }

            AutoTranslate::sync($lang);

            $this->components->info('Syncing translations done successfully.');
        } catch (\Exception $e) {
            $this->components->error($e->getMessage());
        }
    }
}
