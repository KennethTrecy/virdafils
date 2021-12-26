<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class CreateDirectoryTest extends TestCase {
	public function testRootCreation() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/";

		$adapter->createDir($path, new Config([]));

		$this->assertDatabaseHas("directories", [ "name" => "/" ]);
		$this->assertDatabaseCount("directories", 1);
	}

	public function testPathCreation() {
		$adapter = new VirdafilsAdapter([]);
		$path = "/a";

		$adapter->createDir($path, new Config([]));

		$this->assertDatabaseHas("directories", [ "name" => "/" ]);
		$this->assertDatabaseHas("directories", [ "name" => "a" ]);
		$this->assertDatabaseCount("directories", 2);
	}

	public function testPathCreationFromRoot() {
		$adapter = new VirdafilsAdapter([ "root" => "/a" ]);
		$path = "b";

		$adapter->createDir($path, new Config([]));

		$this->assertDatabaseHas("directories", [ "name" => "/" ]);
		$this->assertDatabaseHas("directories", [ "name" => "a" ]);
		$this->assertDatabaseHas("directories", [ "name" => "b" ]);
		$this->assertDatabaseCount("directories", 3);
	}
}
