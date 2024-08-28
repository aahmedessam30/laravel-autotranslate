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

            // use AutoTranslate::setPatterns()->sync($lang); to set extra patterns

            AutoTranslate::sync($lang);

            $this->components->info('Syncing translations done successfully.');
        } catch (\Exception $e) {
            $this->components->error($e->getMessage());
        }
    }
}