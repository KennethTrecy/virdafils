<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;

class GetVisibilityTest extends TestCase {
	public function testRootDirectory() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$directory = Directory::factory()->setPath($path)->create();

		$visibility = $adapter->getVisibility($path);

		$this->assertEquals([
			"path" => $path,
			"visibility" => $directory->visibility
		], $visibility);
	}

	public function testRootFileVisibility() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present_a.txt";
		$file = File::factory()->setPath($path)->create();

		$visibility = $adapter->getVisibility($path);

		$this->assertEquals([
			"path" => $path,
			"visibility" => $file->visibility
		], $visibility);
	}
}
