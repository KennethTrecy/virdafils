<?php

namespace KennethTrecy\Virdafils;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util\MimeType;
use League\Flysystem\RootViolationException;
use Illuminate\Support\Facades\URL;
use KennethTrecy\Virdafils\Util\GeneralHelper;
use KennethTrecy\Virdafils\Util\PathHelper;
use KennethTrecy\Virdafils\Node\Directory;
use KennethTrecy\Virdafils\Node\File;

/**
 * For more information, please read the links below.
 * @link https://github.com/thephpleague/flysystem/blob/1.1.9/src/AdapterInterface.php
 * @link https://github.com/thephpleague/flysystem/blob/1.1.9/src/ReadInterface.php
 */
class VirdafilsAdapter implements AdapterInterface {
	protected Config $configuration;

	function __construct(array $configuration = []) {
		$this->configuration = new Config($configuration);

		GeneralHelper::setFallback($this->configuration);
	}

	public function has($path) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return Directory::navigateByPathParts($path_parts, $this->configuration)->exists()
			||	File::navigateByPathParts($path_parts, $this->configuration)->exists();
	}

	public function getMetadata($path) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);
		$path = PathHelper::join($path_parts);
		$metadata = [ "path" => $path ];

		return $this->whenDirectoryAsPartsExists($path_parts, function($directory) use ($metadata) {
			$metadata["type"] = "dir";
			$metadata["timestamp"] = $directory->first()->updated_at->timestamp;
			return $metadata;
		}, function($path_parts) use ($metadata) {
			return $this->whenFileAsPartsExists($path_parts, function($file) use ($metadata) {
				$metadata["type"] = "file";
				$metadata["size"] = $file->content_size;
				$metadata["timestamp"] = $file->updated_at->timestamp;
				return $metadata;
			});
		});
	}

	public function getSize($path) {
		return $this->getMetadata($path);
	}

	public function getMimeType($path) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return $this->whenFileAsPartsExists($path_parts, function ($file, $resolved_path) {
			$metadata = [
				"type" => "file",
				"path" => $resolved_path,
				"mimetype" => $file->type
			];

			return $metadata;
		});
	}

	public function getTimestamp($path) {
		return $this->getMetadata($path);
	}

	public function listContents($directory = "", $recursive = false) {
		if ($directory === "") $directory = "/";

		return $this->whenDirectoryExists(
			$directory,
			function($directory, $resolved_path, $resolved_path_parts) use ($recursive) {
				$directories = collect([ [ $resolved_path_parts, $directory ] ]);
				$result = collect([]);

				while($directories->count() > 0) {
					[ $local_path_parts, $directory ] = $directories->shift();
					$child_directories = $directory->childDirectories()->get()
						->map(function($child_directory) use (
							$recursive,
							$local_path_parts,
							&$directories
						) {
							$path_parts = [ ...$local_path_parts, $child_directory->name ];

							if ($recursive) {
								$directories->push([ $path_parts, $child_directory ]);
							}

							return [
								"type" => "dir",
								"path" => PathHelper::join($path_parts),
								"timestamp" => $child_directory->updated_at->timestamp
							];
						});

					$child_files = $directory
						->files()
						->get()
						->map(function($file) use ($local_path_parts) {
							$name = $file->name;
							$metadata = [
								"type" => "file",
								"path" => PathHelper::join([ ...$local_path_parts, $name ]),
								"size" => $file->content_size,
								"timestamp" => $file->updated_at->timestamp
							];

							return $metadata;
						});

					$result = $result->merge($child_directories)->merge($child_files);
				}

				return $result->toArray();
			},
			function() { return []; });
	}

	public function getVisibility($path) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);
		$resolved_path = PathHelper::join($path_parts);
		$present_closure = function($model, $resolved_path) {
			return [
				"path" => $resolved_path,
				"visibility" => $model->visibility
			];
		};

		return $this->whenDirectoryAsPartsExists(
			$path_parts,
			$present_closure,
			function ($path_parts) use ($present_closure) {
				return $this->whenFileAsPartsExists($path_parts, $present_closure);
			});
	}

	public function setVisibility($path, $visibility) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);
		$resolved_path = PathHelper::join($path_parts);
		$present_closure = function($model, $resolved_path) use ($visibility) {
			$model->visibility = $visibility;
			if ($model->save()) {
				return [
					"path" => $resolved_path,
					"visibility" => $model->visibility
				];
			} else {
				return false;
			}
		};

		return $this->whenDirectoryAsPartsExists(
			$path_parts,
			$present_closure,
			function ($path_parts) use ($present_closure) {
				return $this->whenFileAsPartsExists($path_parts, $present_closure);
			});
	}

	public function read($path) {
		$file = $this->readStream($path);

		if (is_array($file)) {
			$contents = stream_get_contents($file["stream"]);

			$file["contents"] = $contents;

			if (fclose($file["stream"]) === false) {
				return false;
			}

			unset($file["stream"]);
		}

		return $file;
	}

	public function readStream($path) {
		return $this->whenFileExists($path, function ($file, $resolved_path) {
			$contents = $file->contents;
			if (is_string($contents)) {
				$contents = GeneralHelper::createWrittenMemoryStream($contents);
			}

			return [
				"type" => "file",
				"path" => $resolved_path,
				"stream" => $contents
			];
		});
	}

	public function write($path, $contents, Config $configuration) {
		$result = $this->writeWithType($path, null, $contents, $configuration);
		$result["contents"] = $contents;

		return $result;
	}

	public function writeStream($path, $resource, Config $configuration) {
		$contents = stream_get_contents($resource);
		$metadata = stream_get_meta_data($resource);

		if (fclose($resource)) {
			$type = "text/plain";
			if (isset($metadata["mediatype"])) {
				$type = $metadata["mediatype"];
			} else if ($metadata["wrapper_type"] === "http") {
				$target_header = "Content-Type: ";
				$found_type = null;

				foreach($metadata["wrapper_data"] as $header) {
					if (strpos($header, $target_header) === 0) {
						$header_name_length = strlen($target_header);
						$found_type = substr($header, $header_name_length);
						$semicolon_index = strpos($found_type, ";");
						if ($semicolon_index > 0) {
							$found_type = substr($found_type, 0, $semicolon_index);
						}
						// UNTESTED: What if the stream passed has only semicolon in HTTP `Content-Type`
						// header?
						break;
					}
				}
				if (is_null($found_type)) {
					// TODO: Throw error for malformed HTTP because Content-Type header was not specified
					throw new Exception();
				} else {
					$type = $found_type;
				}
			} else {
				$type = MimeType::detectByFilename($path);
			}

			return $this->writeWithType($path, $type, $contents, $configuration);
		} else {
			return false;
		}
	}

	public function update($path, $contents, Config $configuration) {
		$content_stream = GeneralHelper::createWrittenMemoryStream($contents);

		$info = $this->updateStream($path, $content_stream, $configuration);
		$info["contents"] = $contents;

		return $info;
	}

	public function updateStream($path, $resource, Config $configuration) {
		return $this->whenFileExists($path, function($file, $resolved_path) use ($resource) {
			$contents = $file->contents;

			if (is_string($contents)) {
				$file->contents = stream_get_contents($resource);
			} else {
				$file->contents = $resource;
			}

			if ($file->save() && fclose($resource)) {
				return [
					"type" => "file",
					"path" => $resolved_path,
					"visibility" => $file->visibility
				];
			} else {
				return false;
			}
		});
	}

	public function rename($old_path, $new_path) {
		$path_parts = PathHelper::resolvedSplit($old_path, $this->configuration);

		// TODO: Allow rename for directories
		return $this->whenFileAsPartsExists($path_parts, function ($file) use ($new_path) {
			[
				$directory_path,
				$filename
			] = PathHelper::resolvedSplitDirectoryAndBase($new_path, $this->configuration);

			$directory = $this->findOrCreateDirectory($directory_path, $this->configuration);
			$file->parentDirectory()->associate($directory);
			$file->name = $filename;

			return $file->save();
		});
	}

	public function copy($old_path, $new_path) {
		// TODO: Allow copy for directories
		$stream = $this->readStream($old_path)["stream"];

		return (bool) $this->writeStream($new_path, $stream, $this->configuration);
	}

	public function delete($path) {
		return $this->whenFileExists($path, function ($file, $resolved_path) {
			return $file->delete();
		}, function() { return false; });
	}

	public function createDir($path, Config $configuration) {
		$this->setFallbackConfiguration($configuration);

		$directory_parts = PathHelper::resolvedSplit($path, $configuration);
		return $this->createDirectoryFromParts($directory_parts, $configuration->get("visibility"));
	}

	public function deleteDir($path) {
		$directory_parts = PathHelper::resolvedSplit($path, $this->configuration);

		if ($directory_parts === PathHelper::resolvedSplit(
			$this->configuration->get("root"),
			new Config([ "root" => "/"])
		)) {
			throw new RootViolationException();
		}

		$directory = Directory::navigateByPathParts($directory_parts, $this->configuration)->first();

		if ($directory === null) {
			return false;
		}

		return $directory->delete();
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
	public function getUrl(string $path) {
		$resolved_path = PathHelper::resolve($path, $this->configuration);

		return route("verdafils.stream", [
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
	public function getTemporaryUrl(string $path, $expiration) {
		$resolved_path = PathHelper::resolve($path, $this->configuration);

		return URL::temporarySignedRoute("verdafils.temporary.stream", $expiration, [
			"path" => ltrim($resolved_path, PathHelper::ABSOLUTE_ROOT)
		]);
	}

	protected function writeWithType($path, $type, $contents, Config $configuration) {
		$this->setFallbackConfiguration($configuration);

		[ $directory_path, $filename ] = PathHelper::resolvedSplitDirectoryAndBase(
			$path,
			$configuration);
		$directory = $this->findOrCreateDirectory($directory_path, $configuration);

		if (is_null($type)) {
			$type = MimeType::detectByFilename($filename);
		}

		$visibility = $configuration->get("visibility");
		$directory->files()->firstOrCreate(
			[ "name" => $filename ],
			compact("type", "visibility", "contents")
		);

		return [
			"type" => "file",
			"path" => $path,
			"visibility" => $visibility
		];
	}

	protected function findOrCreateDirectory($directory_path, $configuration) {
		$path_parts = PathHelper::resolvedSplit($directory_path, $configuration);
		$directory_builder = Directory::navigateByPathParts($path_parts, $configuration);
		$directory = $directory_builder->first();

		if (is_null($directory)) {
			$this->createDirectoryFromParts($path_parts, $configuration->get("visibility"));
			$directory = $directory_builder->first();
		};

		return $directory;
	}

	protected function createDirectoryFromParts($directory_parts, $visibility) {
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

	protected function setFallbackConfiguration(Config $configuration) {
		// Prevent setting fallback to itself
		if ($configuration !== $this->configuration) {
			$configuration->setFallback($this->configuration);
		}
	}

	protected function whenDirectoryExists($path, $present_closure, $absent_closure = null) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return $this->whenDirectoryAsPartsExists($path_parts, $present_closure, $absent_closure);
	}

	protected function whenFileExists($path, $present_closure, $absent_closure = null) {
		$path_parts = PathHelper::resolvedSplit($path, $this->configuration);

		return $this->whenFileAsPartsExists($path_parts, $present_closure, $absent_closure);
	}

	protected function whenDirectoryAsPartsExists(
		$resolved_path_parts,
		$present_closure,
		$absent_closure = null
	) {
		return $this->whenModelExists(
			$resolved_path_parts,
			Directory::navigateByPathParts($resolved_path_parts),
			$present_closure,
			$absent_closure);
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
			$absent_closure);
	}

	protected function whenModelExists(
		$resolved_path_parts,
		$builder,
		$present_closure,
		$absent_closure = null
	) {
		$model = $builder->first();
		if (is_null($model)) {
			if (is_null($absent_closure)) {
				// TODO: Throw error when file/directory not found
				return false;
			} else {
				return $absent_closure($resolved_path_parts, $builder);
			}
		} else {
			$resolved_path =  PathHelper::join($resolved_path_parts);
			return $present_closure($model, $resolved_path, $resolved_path_parts);
		}
	}
}
