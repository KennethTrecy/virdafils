<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class DirectoryExistsTest extends TestCase
{
    public function testRootDirectoryPresence()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/";
        $root = Directory::factory()->setPath($path)->create();

        $isPresent = $adapter->directoryExists($path);

        $this->assertTrue($isPresent);
        $this->assertDatabaseCount("directories", 1);
    }

    public function testSubdirectoryPresence()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/a";
        $subdirectory = Directory::factory()->setPath($path)->create();

        $isPresent = $adapter->directoryExists($path);

        $this->assertTrue($isPresent);
        $this->assertDatabaseCount("directories", 2);
    }
}
