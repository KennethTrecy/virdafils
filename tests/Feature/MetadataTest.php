<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;

class MetadataTest extends TestCase {
	public function testRootDirectory() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$file = Directory::factory()->setPath($path)->create();

		$metadata = $adapter->getMetadata($path);

		$this->assertEquals([
			"type" => "dir",
			"path" => $path,
			"timestamp" => $file->updated_at->timestamp
		], $metadata);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 0);
	}

	public function testRootFile() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$file = File::factory()->setPath($path)->create();

		$metadata = $adapter->getMetadata($path);

		$this->assertEquals([
			"type" => "file",
			"path" => $path,
			"size" => strlen($file->contents),
			"timestamp" => $file->updated_at->timestamp
		], $metadata);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
	}
}
