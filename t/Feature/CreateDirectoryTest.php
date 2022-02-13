<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class CreateDirectoryTest extends TestCase
{
    public function testRootCreation()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/";

        $adapter->createDirectory($path, new Config([]));

        $this->assertDatabaseHas("directories", [ "name" => "/" ]);
        $this->assertDatabaseCount("directories", 1);
    }

    public function testPathCreation()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/a";

        $adapter->createDirectory($path, new Config([]));

        $this->assertDatabaseHas("directories", [ "name" => "/" ]);
        $this->assertDatabaseHas("directories", [ "name" => "a" ]);
        $this->assertDatabaseCount("directories", 2);
    }

    public function testPathCreationFromRoot()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "b";

        $adapter->createDirectory($path, new Config([ "root" => "/a" ]));

        $this->assertDatabaseHas("directories", [ "name" => "/" ]);
        $this->assertDatabaseHas("directories", [ "name" => "a" ]);
        $this->assertDatabaseHas("directories", [ "name" => "b" ]);
        $this->assertDatabaseCount("directories", 3);
    }
}
