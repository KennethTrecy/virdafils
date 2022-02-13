<?php

namespace Tests\Feature;

use Tests\TestCase;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class MetadataTest extends TestCase
{
    public function testDeepFileMimeType()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/a/sample.txt";
        $file = File::factory()->setPath($path)->create();

        $attributes = $adapter->mimeType($path);

        $this->assertEquals($file->type, $attributes->mimeType());
        $this->assertDatabaseCount("directories", 2);
        $this->assertDatabaseCount("files", 1);
    }

    public function testRootFileSize()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present.txt";
        $file = File::factory()->setPath($path)->create();

        $attributes = $adapter->fileSize($path);

        $this->assertEquals(strlen($file->contents), $attributes->fileSize());
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 1);
    }

    public function testRootFileLastModified()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present.txt";
        $file = File::factory()->setPath($path)->create();

        $attributes = $adapter->lastModified($path);

        $this->assertEquals($file->updated_at->timestamp, $attributes->lastModified());
        $this->assertDatabaseCount("directories", 1);
        $this->assertDatabaseCount("files", 1);
    }
}
