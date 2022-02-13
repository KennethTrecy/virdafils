<?php

namespace Tests\Integration;

use Tests\TestCase;
use League\Flysystem\Filesystem;
use KennethTrecy\Virdafils\Directory;
use League\Flysystem\FilesystemAdapter;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;

class FullTest extends TestCase
{
    protected static FilesystemAdapterTestCase $tester;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$tester = new class () extends FilesystemAdapterTestCase {
            protected static function createFilesystemAdapter(): FilesystemAdapter
            {
                $configuration = [];
                return new VirdafilsAdapter($configuration);
            }

            public static function setUpAll()
            {
                static::setUpBeforeClass();
            }

            public static function tearDownAll()
            {
                static::tearDownAfterClass();
            }
        };
        get_class(static::$tester)::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        get_class(static::$tester)::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        static::$tester->setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$tester->tearDown();
    }
}
