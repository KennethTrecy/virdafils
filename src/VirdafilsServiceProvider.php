<?php

namespace KennethTrecy\Virdafils;

use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class VirdafilsServiceProvider extends ServiceProvider {
	public function boot() {
		Storage::extend("virdafils", function($app, $configuration) {
			return new Filesystem(new VirdafilsAdapter($configuration));
		});
	}
}
