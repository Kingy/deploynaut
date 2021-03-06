<?php

class DNEnvironmentList extends DataList {
	
	/**
	 *
	 * @var int
	 */
	protected $projectID;

	/**
	 * 
	 * @param int $projectID
	 * @return \DNEnvironmentList
	 */
	public function setProjectID($projectID) {
		$this->projectID = $projectID;
		return $this;
	}

	/**
	 * Sync the in-db project list with a list of file paths
	 * @param array $paths Array of pathnames
	 * @param boolean $remove Should obsolete environments be removed?
	 */
	public function syncWithPaths($paths, $dryRun = false) {

		// Normalise paths in DB
		foreach($this as $item) {
			$real = realpath($item->Filename);
			if($real && $real != $item->Filename) {
				$item->Filename = $real;
				$item->write();
			}
		}
		
		foreach($paths as $path) {
			$path = basename($path);
			if($this->filter('Filename', $path)->count()) {
				continue;
			}
			
			$this->message('Adding "'.basename($path).'" to db');
			if(!$dryRun) {
				$environment = DNEnvironment::create_from_path($path);
				$environment->ProjectID = $this->projectID;
				$environment->write();
			}
		}
	}
	
	/**
	 * Remove environment files that can't be found on disk
	 * 
	 * @param array $paths
	 * @param bool $dryRun
	 */
	public function removeObsolete($paths, $dryRun = false) {
		$basePaths = array_map(function($path){
			return basename($path);
		}, $paths); 
		
		$removeList = $this->filter('Filename:not', $basePaths);
		if($removeList->Count() === 0) {
			return;
		}
		
		foreach($removeList as $remove) {
			$this->message('Removing "'.basename($remove->Name).'" from db');
			if(!$dryRun) {
				$removeList->remove($remove);
			}
		}
	}
	
	/**
	 * 
	 * @param string $text
	 */
	protected function message($text) {
		if(PHP_SAPI !== 'cli') {
			$text = '<p>'.$text.'</p>'.PHP_EOL;
		}
		echo ' - '.$text.PHP_EOL;
	}
}
