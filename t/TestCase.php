<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use KennethTrecy\Virdafils\VirdafilsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase {
	use RefreshDatabase;
	use WithFaker;

	protected $loadEnvironmentVariables = true;

	protected function defineEnvironment($app) {
		$app["config"]->set("filesystems.disks.virdafils", [
			"driver" => "virdafils",
			"root" => "/",
			"visbility" => "private"
		]);
	}

	protected function getPackageProviders($app) {
		return [
			VirdafilsServiceProvider::class
		];
	}

	protected function defineDatabaseMigrations() {
		$this->loadLaravelMigrations();
	}
}
