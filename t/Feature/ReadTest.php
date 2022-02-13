<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class ReadTest extends TestCase
{
    public function testPresentRootFileStream()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present.txt";
        $present_file = File::factory()->setPath($path)->create();

        $readStream = $adapter->readStream($path);

        $this->assertTrue(fclose($readStream));
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 1);
    }

    public function testPresentDeepFileStream()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/a/b/c/present.txt";
        $present_file = File::factory()->setPath($path)->create();

        $readStream = $adapter->readStream($path);

        $this->assertTrue(fclose($readStream));
        $this->assertDatabaseCount("directories", 4);
        $this->assertDatabaseCount("files", 1);
    }

    public function testPresentRootFile()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present.txt";
        $present_file = File::factory()->setPath($path)->create();

        $contents = $adapter->read($path);

        $this->assertEquals($present_file->contents, $contents);
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 1);
    }

    public function testPresentDeepFile()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/a/b/c/present.txt";
        $present_file = File::factory()->setPath($path)->create();

        $contents = $adapter->read($path);

        $this->assertEquals($present_file->contents, $contents);
        $this->assertDatabaseCount("directories", 4);
        $this->assertDatabaseCount("files", 1);
    }
}
