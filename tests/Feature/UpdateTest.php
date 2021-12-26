<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;

class updateTest extends TestCase {
	public function testPresentRootFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$file = File::factory()->setPath($path)->create();
		$new_contents = $this->faker->word();
		$new_content_stream = GeneralHelper::createDataStream("text/plain", $new_contents);

		$updateInfo = $adapter->updateStream($path, $new_content_stream, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => $file->visibility
		], $updateInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => "present.txt",
			"contents" => base64_encode($new_contents)
		]);
	}

	public function testPresentDeepFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c/present.txt";
		$file = File::factory()->setPath($path)->create();
		$new_contents = $this->faker->word();
		$new_content_stream = GeneralHelper::createDataStream("text/plain", $new_contents);

		$updateInfo = $adapter->updateStream($path, $new_content_stream, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => $file->visibility
		], $updateInfo);
		$this->assertDatabaseCount("directories", 4);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => "present.txt",
			"contents" => base64_encode($new_contents)
		]);
	}

	public function testPresentRootFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$file = File::factory()->setPath($path)->create();
		$new_contents = $this->faker->word();

		$updateInfo = $adapter->update($path, $new_contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => $file->visibility,
			"contents" => $new_contents
		], $updateInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => "present.txt",
			"contents" => base64_encode($new_contents)
		]);
	}

	public function testPresentDeepFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c/present.txt";
		$file = File::factory()->setPath($path)->create();
		$new_contents = $this->faker->word();

		$updateInfo = $adapter->update($path, $new_contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => $file->visibility,
			"contents" => $new_contents
		], $updateInfo);
		$this->assertDatabaseCount("directories", 4);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "text/plain",
			"contents" => base64_encode($new_contents)
		]);
	}
}
