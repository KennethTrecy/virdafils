<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;

class ReadTest extends TestCase {
	public function testPresentRootFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$readInfo = $adapter->readStream($path);

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"stream" => $readInfo["stream"]
		], $readInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);

		fclose($readInfo["stream"]);
	}

	public function testPresentDeepFileStream() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$readInfo = $adapter->readStream($path);

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"stream" => $readInfo["stream"]
		], $readInfo);
		$this->assertDatabaseCount("directories", 4);
		$this->assertDatabaseCount("files", 1);

		fclose($readInfo["stream"]);
	}

	public function testPresentRootFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$readInfo = $adapter->read($path);

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"contents" => $present_file->contents
		], $readInfo);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
	}

	public function testPresentDeepFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$readInfo = $adapter->read($path);

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"contents" => $present_file->contents
		], $readInfo);
		$this->assertDatabaseCount("directories", 4);
		$this->assertDatabaseCount("files", 1);
	}
}
