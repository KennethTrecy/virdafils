<?php

namespace KennethTrecy\Virdafils;

use League\Flysystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class VirdafilsServiceProvider extends ServiceProvider {
	public function boot() {
		$this->loadMigrationsFrom(__DIR__."/../database/migrations");

		Storage::extend("virdafils", function($app, $configuration) {
			return new Filesystem(new VirdafilsAdapter($configuration));
		});

		Route::middleware("web")->group(function() {
			Route::get("/storage/{path}", function(string $path) {
				return Storage::download($path);
			})
			->where("path", ".+")
			->name("verdafils.stream");

			Route::get("/temporary/storage/{path}", function(Request $request, string $path) {
				if ($request->hasValidSignature()) {
					return Storage::download($path);
				}

				abort(401);
			})
			->where("path", ".+")
			->name("verdafils.temporary.stream");
		});
	}
}
