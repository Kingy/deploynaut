<?php

/**
 * Runs a capistrano job that will check the connection and that all folders with 
 * permission are setup correctly
 */
class PingJob {

	/**
	 *
	 * @var array
	 */
	public $args;

	/**
	 * 
	 * @global array $databaseConfig
	 */
	public function setUp() {
		global $databaseConfig;
		DB::connect($databaseConfig);
		chdir(BASE_PATH);
	}

	/**
	 * 
	 * @return DNData
	 */
	public function DNData() {
		return Injector::inst()->get('DNData');
	}

	/**
	 * Do the actual job by calling the appropiate backend
	 */
	public function perform() {
		echo "[-] PingJob starting" . PHP_EOL;
		$log = new DeploynautLogFile($this->args['logfile']);
		$DNProject = $this->DNData()->DNProjectList()->filter('Name', $this->args['projectName'])->First();
		$this->DNData()->Backend()->ping($this->args['environment'], $log, $DNProject);
	}
}