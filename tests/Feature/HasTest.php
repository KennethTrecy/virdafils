<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Node\File;

class HasTest extends TestCase {
	public function testRootDirectoryPresence() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$root = Directory::factory()->setPath($path)->create();

		$isPresent = $adapter->has($path);

		$this->assertTrue($isPresent);
		$this->assertDatabaseCount("directories", 1);
	}

	public function testSubdirectoryPresence() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a";
		$subdirectory = Directory::factory()->setPath($path)->create();

		$isPresent = $adapter->has($path);

		$this->assertTrue($isPresent);
		$this->assertDatabaseCount("directories", 2);
	}

	public function testSubdirectoryAbsence() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a";
		$present_subdirectory = Directory::factory()->setPath("/b")->create();
		$absent_subdirectory = Directory::factory()
			->setPath($path, $present_subdirectory->parentDirectory)
			->make();

		$isPresent = $adapter->has($path);

		$this->assertFalse($isPresent);
		$this->assertDatabaseCount("directories", 2);
	}

	public function testFilePresence() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/present.txt";
		$present_file = File::factory()->setPath($path)->create();

		$isPresent = $adapter->has($path);

		$this->assertTrue($isPresent);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 1);
	}

	public function testFileAbsence() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/absent.txt";
		$absent_file = File::factory()->setPath($path)->make();

		$isPresent = $adapter->has($path);

		$this->assertFalse($isPresent);
		$this->assertDatabaseCount("directories", 1);
		$this->assertDatabaseCount("files", 0);
	}
}
