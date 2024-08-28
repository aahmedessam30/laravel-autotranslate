<?php

namespace Ahmedessam\LaravelAutotranslate;

class StubGenerator
{
    public static function getStub($stub, $path = null): false|string
    {
        $stub = str($stub)->when(
            str($stub)->endsWith('.stub'),
            fn($stub) => str($stub)->before('.stub')
        )->value();

        $path = str($path)->when(
            str($path)->startsWith(base_path()),
            fn($path) => $path,
            fn($path) => str(base_path("$path/stubs"))
        )->value();

        return file_get_contents("$path/$stub.stub");
    }

    public static function replaceStub($stub, $keys, $values): array|string
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        return str_replace($keys, $values, $stub);
    }

    public static function saveStub($path, $stub): void
    {
        file_put_contents($path, $stub);
    }

    public static function generate($stub, $keys, $values, $path): void
    {
        $stub = self::getStub($stub);
        $stub = self::replaceStub($stub, $keys, $values);
        self::saveStub($path, $stub);
    }
}
