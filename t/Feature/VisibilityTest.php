<?php

namespace Tests\Feature;

use Tests\TestCase;
use League\Flysystem\Config;
use KennethTrecy\Virdafils\Node\File;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\VirdafilsAdapter;

class VisibilityTest extends TestCase
{
    public function testRootDirectory()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/";
        $directory = Directory::factory()->setPath($path)->create();

        $attributes = $adapter->visibility($path);

        $this->assertEquals($directory->visibility, $attributes->visibility());
    }

    public function testRootFileVisibility()
    {
        $adapter = new VirdafilsAdapter([]);
        $path = "/present_a.txt";
        $file = File::factory()->setPath($path)->create();

        $attributes = $adapter->visibility($path);

        $this->assertEquals($file->visibility, $attributes->visibility());
    }
}
