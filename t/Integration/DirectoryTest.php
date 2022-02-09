<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\RootViolationException;
use KennethTrecy\Virdafils\Node\Directory;

class DirectoryTest extends TestCase {
	public function testRootCreation() {
		$path = "/";

		Storage::makeDirectory($path);

		$this->assertDatabaseHas("directories", [ "name" => "/" ]);
		$this->assertDatabaseCount("directories", 1);
	}

	public function testDeepPathCreation() {
		$path = "/a/b/c";

		Storage::makeDirectory($path);

		$this->assertDatabaseHas("directories", [ "name" => "/" ]);
		$this->assertDatabaseHas("directories", [ "name" => "a" ]);
		$this->assertDatabaseHas("directories", [ "name" => "b" ]);
		$this->assertDatabaseHas("directories", [ "name" => "c" ]);
		$this->assertDatabaseCount("directories", 4);
	}

	public function testRootDeletion() {
		$path = "/";
		$root = Directory::factory()->setPath($path)->create();

		$this->expectException(RootViolationException::class);

		$hasDeleted = Storage::deleteDirectory($path);
	}

	public function testDeepPathsDeletion() {
		$path = "/a/b";
		$subdirectory = Directory::factory()->setPath($path)->create();

		$hasDeleted = Storage::deleteDirectory($path);

		$this->assertTrue($hasDeleted);
		$this->assertDeleted($subdirectory);
		$this->assertModelExists($subdirectory->parentDirectory);
		$this->assertModelExists($subdirectory->parentDirectory->parentDirectory);
		$this->assertDatabaseCount("directories", 2);
	}
}
