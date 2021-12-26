<?php

namespace KennethTrecy\Virdafils\Node;

use KennethTrecy\Elomocato\Base64File;

class File extends AbstractNodeModel {
	protected $fillable = [
		"name",
		"type",
		"contents",
		"visibility"
	];

	protected $casts = [
		"contents" => Base64File::class
	];
}
