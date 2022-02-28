<?php

namespace KennethTrecy\Virdafils;

use Illuminate\Http\Request;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemAdapter;
use KennethTrecy\Virdafils\Util\GeneralHelper;

class VirdafilsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__."/../database/migrations");

        $configuration = __DIR__ . "/../config/virdafils.php";
        $this->mergeConfigFrom($configuration, "virdafils");
        if ($this->app->runningInConsole()) {
            $this->publishes([$configuration => config_path("virdafils.php")], "virdafils");
        }

        Storage::extend("virdafils", function ($app, $configuration) {
            $configuration = array_merge(GeneralHelper::$fallback, $configuration);

            $adapter = new VirdafilsAdapter($configuration);
            return new FilesystemAdapter(
                new Filesystem($adapter, $configuration),
                $adapter,
                $configuration
            );
        });

        Route::middleware(config("virdafils.middleware"))
        ->group(function () {
            Route::get("/storage/{path}", function (string $path) {
                return Storage::download($path);
            })
            ->where("path", ".+")
            ->name("virdafils.stream");

            Route::get("/temporary/storage/{path}", function (Request $request, string $path) {
                if ($request->hasValidSignature()) {
                    return Storage::download($path);
                }

                abort(401);
            })
            ->where("path", ".+")
            ->name("virdafils.temporary.stream");
        });
    }
}
