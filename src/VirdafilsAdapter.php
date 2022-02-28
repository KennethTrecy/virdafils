<?php

namespace KennethTrecy\Virdafils;

use League\Flysystem\Config;
use Illuminate\Support\Facades\URL;
use League\Flysystem\FileAttributes;
use KennethTrecy\Virdafils\Node\File;
use League\Flysystem\UnableToMoveCopy;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FilesystemException;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Util\PathHelper;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToDeleteDirectory;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use League\Flysystem\UnableToRetrieveMetadata;

/**
 * Provides the adapter to CRUD file(s) from/to database.
 */
class VirdafilsAdapter implements FilesystemAdapter
{
    protected Config $configuration;

    public function __construct(array $configuration = [])
    {
        $this->configuration = new Config($configuration);

        $this->configuration = GeneralHelper::withDefaults($this->configuration);
    }

    public function fileExists(string $path): bool
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);

        return File::navigateByPathParts($path_parts, $this->configuration)->exists();
    }

    public function directoryExists(string $path): bool
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);

        return Directory::navigateByPathParts($path_parts, $this->configuration)->exists();
    }

    public function write(string $path, string $contents, Config $configuration): void
    {
        $this->writeWithType($path, null, $contents, $configuration);
    }

    public function writeStream(string $path, $resource, Config $configuration): void
    {
        $contents = stream_get_contents($resource);
        $metadata = stream_get_meta_data($resource);

        if (fclose($resource)) {
            $type = "text/plain";
            if (isset($metadata["mediatype"])) {
                $type = $metadata["mediatype"];
            } elseif ($metadata["wrapper_type"] === "http") {
                $target_header = "Content-Type: ";
                $found_type = null;

                foreach ($metadata["wrapper_data"] as $header) {
                    if (strpos($header, $target_header) === 0) {
                        $header_name_length = strlen($target_header);
                        $found_type = substr($header, $header_name_length);
                        $semicolon_index = strpos($found_type, ";");
                        if ($semicolon_index > 0) {
                            $found_type = trim(substr($found_type, 0, $semicolon_index));
                        }
                        break;
                    }
                }
                if (is_null($found_type)) {
                    throw UnableToWriteFile::atLocation(
                        $path,
                        "The HTTP Content-Type header is missing."
                    );
                } elseif ($found_type === "") {
                    throw UnableToWriteFile::atLocation(
                        $path,
                        "The HTTP Content-Type header is malformed."
                    );
                } else {
                    $type = $found_type;
                }
            } else {
                $type = GeneralHelper::detectMimeType($path);
            }

            $this->writeWithType($path, $type, $contents, $configuration);
        } else {
            throw UnableToWriteFile::atLocation($path, "The stream did not close successfully.");
        }
    }

    public function read(string $path): string
    {
        $stream = $this->readStream($path);
        $contents = stream_get_contents($stream);

        if (fclose($stream) === false) {
            throw UnableToReadFile::fromLocation($path, "The stream did not close successfully.");
        }

        return $contents;
    }

    public function readStream(string $path)
    {
        return $this->whenFileExists($path, function ($file, $resolved_path) {
            $contents = $file->contents;
            if (is_string($contents)) {
                $contents = GeneralHelper::createWrittenMemoryStream($contents);
            }

            return $contents;
        }, function () use ($path) {
            throw UnableToReadFile::fromLocation($path, "The file does not exists.");
        });
    }

    public function delete(string $path): void
    {
        $this->whenFileExists($path, function ($file, $resolved_path) {
            return $file->delete();
        }, function () {
            return;
        });
    }

    public function deleteDirectory(string $path): void
    {
        $directory_parts = PathHelper::resolvedSplit($path, $this->configuration);

        if ($directory_parts === PathHelper::resolvedSplit(
            $this->configuration->get("root"),
            $this->configuration
        )) {
            throw UnableToDeleteDirectory::atLocation($path, "It is the root directory.");
        }

        $directory = Directory::navigateByPathParts($directory_parts, $this->configuration)->first();

        if (is_null($directory)) {
            throw UnableToDeleteDirectory::atLocation($path, "Path does not exists.");
        }

        $directory->delete();
    }

    public function createDirectory(string $path, Config $configuration): void
    {
        $configuration = GeneralHelper::withDefaults($configuration);
        $directory_parts = PathHelper::resolvedSplit($path, $configuration);
        $this->createDirectoryFromParts($directory_parts, $configuration->get("visibility"));
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);
        $resolved_path = PathHelper::join($path_parts);
        $present_closure = function ($model) use ($path, $visibility) {
            $model->visibility = $visibility;
            if (!$model->save()) {
                throw UnableToSetVisibility::atLocation($path, "Probably a database error.");
            }
        };

        $this->whenDirectoryAsPartsExists(
            $path_parts,
            $present_closure,
            function ($path_parts) use ($path, $present_closure) {
                $this->whenFileAsPartsExists(
                    $path_parts,
                    $present_closure,
                    function () use ($path) {
                        throw UnableToSetVisibility::atLocation($path, "Path does not exists.");
                    }
                );
            }
        );
    }

    public function visibility(string $path): FileAttributes
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);
        $present_closure = function ($model, $resolved_path) use ($path) {
            return new FileAttributes($path, null, $model->visibility);
        };

        return $this->whenDirectoryAsPartsExists(
            $path_parts,
            $present_closure,
            function ($path_parts) use ($path, $present_closure) {
                return $this->whenFileAsPartsExists(
                    $path_parts,
                    $present_closure,
                    function () use ($path) {
                        throw UnableToRetrieveMetadata::visibility($path, "Path does not exists.");
                    }
                );
            }
        );
    }

    public function mimeType(string $path): FileAttributes
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);

        return $this->whenFileAsPartsExists($path_parts, function ($file) use ($path) {
            if (is_null($file->type)) {
                throw UnableToRetrieveMetadata::mimeType($path, "File has an unknown MIME type.");
            }

            return new FileAttributes($path, null, null, null, $file->type);
        }, function () use ($path) {
            throw UnableToRetrieveMetadata::mimeType($path, "File does not exists.");
        });
    }

    public function fileSize(string $path): FileAttributes
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);

        return $this->whenFileAsPartsExists($path_parts, function ($file) use ($path) {
            return new FileAttributes($path, $file->content_size);
        }, function () use ($path) {
            throw UnableToRetrieveMetadata::fileSize($path, "File does not exists.");
        });
    }

    public function lastModified(string $path): FileAttributes
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);
        $present_closure = function ($model) use ($path) {
            return new FileAttributes($path, null, null, $model->updated_at->timestamp);
        };

        return $this->whenDirectoryAsPartsExists(
            $path_parts,
            $present_closure,
            function () use ($path, $path_parts, $present_closure) {
                return $this->whenFileAsPartsExists(
                    $path_parts,
                    $present_closure,
                    function () use ($path) {
                        throw UnableToRetrieveMetadata::lastModified(
                            $path,
                            "Path does not exists."
                        );
                    }
                );
            }
        );
    }

    public function listContents(string $directory, $recursive = true): iterable
    {
        yield from $this->whenDirectoryExists(
            $directory,
            function ($directory, $resolved_path, $resolved_path_parts) use ($recursive) {
                $directories = collect([ [ $resolved_path_parts, $directory ] ]);
                while ($directories->count() > 0) {
                    [ $local_path_parts, $directory ] = $directories->shift();
                    $child_directories = $directory->childDirectories()->get();
                    foreach ($child_directories as $child_directory) {
                        $path_parts = [ ...$local_path_parts, $child_directory->name ];

                        if ($recursive) {
                            $directories->push([ $path_parts, $child_directory ]);
                        }

                        yield new DirectoryAttributes(
                            PathHelper::join($path_parts),
                            $child_directory->visibility,
                            $child_directory->updated_at->timestamp
                        );
                    }

                    $child_files = $directory->files()->get();
                    foreach ($child_files as $child_file) {
                        $name = $child_file->name;
                        yield new FileAttributes(
                            PathHelper::join([ ...$local_path_parts, $name ]),
                            $child_file->content_size,
                            $child_file->visibility,
                            $child_file->updated_at->timestamp,
                            $child_file->type
                        );
                    }
                }
            },
            function () {
                throw UnableToRetrieveMetadata::size($path, "Path does not exists.");
            }
        );
    }

    public function move(string $old_path, string $new_path, Config $configuration): void
    {
        $path_parts = PathHelper::resolvedSplit($old_path, $this->configuration);

        $this->whenFileAsPartsExists($path_parts, function ($file) use ($old_path, $new_path) {
            [
                $directory_path,
                $filename
            ] = PathHelper::resolvedSplitDirectoryAndBase($new_path, $this->configuration);

            $directory = $this->findOrCreateDirectory($directory_path, $this->configuration);
            $file->parentDirectory()->associate($directory);
            $file->name = $filename;

            if (!$file->save()) {
                throw UnableToMoveFile::fromLocationTo(
                    $old_path,
                    $new_path,
                    "Cannot save the file to the database."
                );
            }
        }, function () use ($old_path, $new_path) {
            throw UnableToMoveFile::fromLocationTo(
                $old_path,
                $new_path
            );
        });
    }

    public function copy(string $old_path, string $new_path, Config $configuration): void
    {
        try {
            $stream = $this->readStream($old_path);
            $configuration = $configuration->extend([ "visibility" => "public" ]);
            $this->writeStream($new_path, $stream, $configuration);
        } catch (FilesystemException $error) {
            throw UnableToCopyFile::fromLocationTo(
                $old_path,
                $new_path,
                $error
            );
        }
    }

    /**
     * Generates the URL so the file can be downloaded.
     *
     * This is not required by the adapter interface but necessary to make the static method `url()`
     * of `\Illuminate\Support\Facades\Storage` work.
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path)
    {
        $resolved_path = PathHelper::resolve($path, $this->configuration);

        return route("virdafils.stream", [
            "path" => ltrim($resolved_path, PathHelper::ABSOLUTE_ROOT)
        ]);
    }

    /**
     * Generates a temporary URL so the file can be accessed.
     *
     * This is not required by the adapter interface but necessary to make the static method
     * `temporaryURL()` of `\Illuminate\Support\Facades\Storage` work.
     *
     * @param string $path
     * @param \DateTimeInterface $expiration
     * @return string
     */
    public function getTemporaryUrl(string $path, $expiration)
    {
        $resolved_path = PathHelper::resolve($path, $this->configuration);

        return URL::temporarySignedRoute("virdafils.temporary.stream", $expiration, [
            "path" => ltrim($resolved_path, PathHelper::ABSOLUTE_ROOT)
        ]);
    }

    protected function writeWithType(string $path, $type, $contents, Config $configuration): void
    {
        $configuration = GeneralHelper::withDefaults($configuration);
        [ $directory_path, $filename ] = PathHelper::resolvedSplitDirectoryAndBase(
            $path,
            $configuration
        );
        $directory = $this->findOrCreateDirectory($directory_path, $configuration);

        if (is_null($type)) {
            $type = GeneralHelper::detectMimeType($filename);
        }

        $visibility = $configuration->get("visibility");
        $directory->files()->updateOrCreate(
            [ "name" => $filename ],
            compact("type", "visibility", "contents")
        );
    }

    protected function findOrCreateDirectory($directory_path, $configuration)
    {
        $path_parts = PathHelper::resolvedSplit($directory_path, $configuration);
        $directory_builder = Directory::navigateByPathParts($path_parts, $configuration);
        $directory = $directory_builder->first();

        if (is_null($directory)) {
            $this->createDirectoryFromParts($path_parts, $configuration->get("visibility"));
            $directory = $directory_builder->first();
        };

        return $directory;
    }

    protected function createDirectoryFromParts($directory_parts, $visibility)
    {
        $resolved_path = PathHelper::join($directory_parts);

        $directory = Directory::firstOrCreate([
            "name" => array_shift($directory_parts),
            "visibility" => $visibility
        ]);

        foreach ($directory_parts as $directory_name) {
            $directory = $directory->childDirectories()->firstOrCreate([
                "name" => $directory_name
            ], [
                "visibility" => $visibility
            ]);
        }

        // TODO: Return false if the path is a file
        return [
            "path" => $resolved_path,
            "type" => "dir"
        ];
    }

    protected function getDefaultConfiguration(Config $configuration)
    {
        // Prevent setting fallback to itself
        if ($configuration !== $this->configuration) {
            return $this->configuration;
        } else {
            return $configuration;
        }
    }

    protected function whenDirectoryExists($path, $present_closure, $absent_closure = null)
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);

        return $this->whenDirectoryAsPartsExists($path_parts, $present_closure, $absent_closure);
    }

    protected function whenFileExists($path, $present_closure, $absent_closure = null)
    {
        $path_parts = PathHelper::resolvedSplit($path, $this->configuration);

        return $this->whenFileAsPartsExists($path_parts, $present_closure, $absent_closure);
    }

    protected function whenDirectoryAsPartsExists(
        $resolved_path_parts,
        $present_closure,
        $absent_closure
    ) {
        return $this->whenModelExists(
            $resolved_path_parts,
            Directory::navigateByPathParts($resolved_path_parts),
            $present_closure,
            $absent_closure
        );
    }

    protected function whenFileAsPartsExists(
        $resolved_path_parts,
        $present_closure,
        $absent_closure = null
    ) {
        return $this->whenModelExists(
            $resolved_path_parts,
            File::navigateByPathParts($resolved_path_parts),
            $present_closure,
            $absent_closure ?? function () use ($path) {
                throw UnableToReadFile::fromLocation($path, "The file does not exists.");
            }
        );
    }

    protected function whenModelExists(
        $resolved_path_parts,
        $builder,
        $present_closure,
        $absent_closure
    ) {
        $model = $builder->first();
        if (is_null($model)) {
            return $absent_closure($resolved_path_parts, $builder);
        } else {
            $resolved_path =  PathHelper::join($resolved_path_parts);
            return $present_closure($model, $resolved_path, $resolved_path_parts);
        }
    }
}
