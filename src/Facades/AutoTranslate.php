<?php

namespace Ahmedessam\LaravelAutotranslate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static sync(array|bool|string|null $lang)
 * @method static setPatterns()
 */
class AutoTranslate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'auto-translate';
    }
}
