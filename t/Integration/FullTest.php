<?php

namespace Tests\Integration;

use Generator;
use Tests\TestCase;
use League\Flysystem\Filesystem;
use KennethTrecy\Virdafils\Directory;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToSetVisibility;
use KennethTrecy\Virdafils\VirdafilsAdapter;
use League\Flysystem\UnableToRetrieveMetadata;
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

    public function test_writing_and_reading_with_string()
    {
        static::$tester->writing_and_reading_with_string();
    }

    public function test_writing_a_file_with_a_stream()
    {
        static::$tester->writing_a_file_with_a_stream();
    }

    public function test_writing_and_reading_files_with_special_path()
    {
        foreach (static::$tester->filenameProvider() as $name => $arguments) {
            static::$tester->writing_and_reading_files_with_special_path(...$arguments);
        }
    }

    public function test_writing_a_file_with_an_empty_stream()
    {
        static::$tester->writing_a_file_with_an_empty_stream();
    }

    public function test_reading_a_file()
    {
        static::$tester->reading_a_file();
    }

    public function test_reading_a_file_with_a_stream()
    {
        static::$tester->reading_a_file_with_a_stream();
    }

    public function test_overwriting_a_file()
    {
        static::$tester->overwriting_a_file();
    }

    public function test_deleting_a_file()
    {
        static::$tester->deleting_a_file();
    }

    public function test_listing_contents_shallow()
    {
        static::$tester->listing_contents_shallow();
    }

    public function test_listing_contents_recursive()
    {
        static::$tester->listing_contents_recursive();
    }

    public function test_fetching_file_size()
    {
        static::$tester->fetching_file_size();
    }

    public function test_setting_visibility()
    {
        static::$tester->setting_visibility();
    }

    public function test_fetching_file_size_of_a_directory()
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        static::$tester->fetching_file_size_of_a_directory();
    }

    public function test_fetching_file_size_of_non_existing_file()
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        static::$tester->fetching_file_size_of_non_existing_file();
    }

    public function test_fetching_last_modified_of_non_existing_file()
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        static::$tester->fetching_last_modified_of_non_existing_file();
    }

    public function test_fetching_visibility_of_non_existing_file()
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        static::$tester->fetching_visibility_of_non_existing_file();
    }

    public function test_fetching_the_mime_type_of_an_svg_file()
    {
        static::$tester->fetching_the_mime_type_of_an_svg_file();
    }

    public function test_fetching_mime_type_of_non_existing_file()
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        static::$tester->fetching_mime_type_of_non_existing_file();
    }

    public function test_fetching_unknown_mime_type_of_a_file()
    {
        $this->expectException(UnableToRetrieveMetadata::class);
        static::$tester->fetching_unknown_mime_type_of_a_file();
    }

    public function test_listing_a_toplevel_directory()
    {
        static::$tester->listing_a_toplevel_directory();
    }

    public function test_writing_and_reading_with_streams()
    {
        static::$tester->writing_and_reading_with_streams();
    }

    public function test_setting_visibility_on_a_file_that_does_not_exist()
    {
        $this->expectException(UnableToSetVisibility::class);
        static::$tester->setting_visibility_on_a_file_that_does_not_exist();
    }

    public function test_copying_a_file()
    {
        static::$tester->copying_a_file();
    }

    public function test_copying_a_file_again()
    {
        static::$tester->copying_a_file_again();
    }

    public function test_moving_a_file()
    {
        static::$tester->moving_a_file();
    }

    public function test_reading_a_file_that_does_not_exist()
    {
        $this->expectException(UnableToReadFile::class);
        static::$tester->reading_a_file_that_does_not_exist();
    }

    public function test_moving_a_file_that_does_not_exist()
    {
        $this->expectException(UnableToMoveFile::class);
        static::$tester->moving_a_file_that_does_not_exist();
    }

    public function test_trying_to_delete_a_non_existing_file()
    {
        static::$tester->trying_to_delete_a_non_existing_file();
    }

    public function test_checking_if_files_exist()
    {
        static::$tester->checking_if_files_exist();
    }

    public function test_fetching_last_modified()
    {
        static::$tester->fetching_last_modified();
    }

    public function test_failing_to_read_a_non_existing_file_into_a_stream()
    {
        $this->expectException(UnableToReadFile::class);
        static::$tester->failing_to_read_a_non_existing_file_into_a_stream();
    }

    public function test_failing_to_read_a_non_existing_file()
    {
        $this->expectException(UnableToReadFile::class);
        static::$tester->failing_to_read_a_non_existing_file();
    }

    public function test_creating_a_directory()
    {
        static::$tester->creating_a_directory();
    }

    public function test_copying_a_file_with_collision()
    {
        static::$tester->copying_a_file_with_collision();
    }
}
