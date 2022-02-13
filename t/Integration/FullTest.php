<?php

namespace Tests\Integration;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;

class FullTest extends FilesystemAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $configuration = [];
        return new VirdafilsAdapter($configuration);
    }
}
