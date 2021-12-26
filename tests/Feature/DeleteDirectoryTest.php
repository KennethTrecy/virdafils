<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use League\Flysystem\RootViolationException;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use KennethTrecy\Virdafils\Node\Directory;

class DeleteDirectoryTest extends TestCase {
	public function testRootDeletion() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";
		$root = Directory::factory()->setPath($path)->create();

		$this->expectException(RootViolationException::class);

		$adapter->deleteDir($path);
	}

	public function testChildDirectoryDeletion() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a";
		$subdirectory = Directory::factory()->setPath($path)->create();

		$hasDeleted = $adapter->deleteDir($path);

		$this->assertTrue($hasDeleted);
		$this->assertDeleted($subdirectory);
		$this->assertModelExists($subdirectory->parentDirectory);
		$this->assertDatabaseCount("directories", 1);
	}

	public function testDeepPathsDeletion() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a/b/c";
		$subdirectory = Directory::factory()->setPath($path)->create();

		$hasDeleted = $adapter->deleteDir($path);

		$this->assertTrue($hasDeleted);
		$this->assertDeleted($subdirectory);
		$this->assertModelExists($subdirectory->parentDirectory);
		$this->assertModelExists($subdirectory->parentDirectory->parentDirectory);
		$this->assertModelExists($subdirectory->parentDirectory->parentDirectory->parentDirectory);
		$this->assertDatabaseCount("directories", 3);
	}
}
