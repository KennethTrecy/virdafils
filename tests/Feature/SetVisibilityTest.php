<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;

class SetVisibilityTest extends TestCase {
	public function testRootDirectory() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$directory = Directory::factory()->setPath($path)->create();
		$new_visibility = "public";

		$visibility = $adapter->setVisibility($path, $new_visibility);

		$this->assertEquals([
			"path" => $path,
			"visibility" => $new_visibility
		], $visibility);
	}

	public function testRootFileVisibility() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present_a.txt";
		$file = File::factory()->setPath($path)->create();
		$new_visibility = "public";

		$visibility = $adapter->setVisibility($path, $new_visibility);

		$this->assertEquals([
			"path" => $path,
			"visibility" => $new_visibility
		], $visibility);
	}
}
