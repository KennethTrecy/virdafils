<?php

namespace KennethTrecy\Virdafils\Node;

class Directory extends AbstractNodeModel {
	protected $fillable = [
		"name",
		"visibility"
	];

	public function childDirectories() {
		return $this->hasMany(Directory::class, "directory_id");
	}
}
