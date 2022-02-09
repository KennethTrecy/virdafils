<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;

class WriteTest extends TestCase {
	public function testPresentRootFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$file = File::factory()->streamContents()->setPath($path)->make();

		$writeInfo = $adapter->writeStream($path, $file->contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => "private"
		], $writeInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
	}

	public function testPresentDeepFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c/present.txt";
		$file = File::factory()->streamContents()->setPath($path)->make();

		$writeInfo = $adapter->writeStream($path, $file->contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => "private"
		], $writeInfo);
		$this->assertDatabaseCount("directories", 4);
		$this->assertDatabaseCount("files", 1);
	}

	public function testPresentRootFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$file = File::factory()->setPath($path)->make();

		$writeInfo = $adapter->write($path, $file->contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => "private",
			"contents" => $file->contents
		], $writeInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
	}

	public function testPresentDeepFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c/present.txt";
		$file = File::factory()->setPath($path)->make();

		$writeInfo = $adapter->write($path, $file->contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => "private",
			"contents" => $file->contents
		], $writeInfo);
		$this->assertDatabaseCount("directories", 4);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "text/plain",
			"contents" => base64_encode($file->contents)
		]);
	}

	public function testExternalFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.png";
		$file = File::factory()->setPath($path)->streamContents()->make();
		$copied_stream = GeneralHelper::createMemoryStream();
		stream_copy_to_stream($file->contents, $copied_stream);
		$contents = stream_get_contents($copied_stream);

		$writeInfo = $adapter->writeStream($path, $file->contents, new Config([]));

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"visibility" => "private"
		], $writeInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
		$this->assertDatabaseHas("files", [
			"name" => basename($path),
			"type" => "image/png",
			"contents" => $contents
		]);
	}
}
