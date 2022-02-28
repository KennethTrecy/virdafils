<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class FileExistsTest extends TestCase
{
    public function testSubdirectoryAbsence()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/a";
        $present_subdirectory = Directory::factory()->setPath("/b")->create();
        $absent_subdirectory = Directory::factory()
            ->setPath($path, $present_subdirectory->parentDirectory)
            ->make();

        $isPresent = $adapter->fileExists($path);

        $this->assertFalse($isPresent);
        $this->assertDatabaseCount("directories", 2);
    }

    public function testFilePresence()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present.txt";
        $present_file = File::factory()->setPath($path)->create();

        $isPresent = $adapter->fileExists($path);

        $this->assertTrue($isPresent);
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 1);
    }

    public function testFileAbsence()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/absent.txt";
        $absent_file = File::factory()->setPath($path)->make();

        $isPresent = $adapter->fileExists($path);

        $this->assertFalse($isPresent);
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 0);
    }
}
