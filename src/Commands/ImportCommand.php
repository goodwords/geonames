<?php namespace Ipalaus\Geonames\Commands;

use ZipArchive;
use ErrorException;
use RuntimeException;
use Ipalaus\Geonames\Importer;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class ImportCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'geonames:import';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import and seed the geonames database with fresh records.';

	/**
	 * Importer instance.
	 *
	 * @var \Ipalaus\Geonames\Importer
	 */
	protected $importer;

	/**
	 * Filesystem implementation.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * File archive instance.
	 *
	 * @var \ZipArchive
	 */
	protected $archive;

	/**
	 * Configuration options.
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Create a new console command instance.
	 *
	 * @param  \Ipalaus\Geonames\Importer         $importer
	 * @param  \Illuminate\Filesystem\Filesystem  $filesystem
	 * @return void
	 */
	public function __construct(Importer $importer, Filesystem $filesystem, array $config)
	{
		$this->importer = $importer;
		$this->filesystem = $filesystem;
		$this->config = $config;

		$this->archive = new ZipArchive;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$names = $this->argument('names');

		// Default choice.
		if (empty($names)) {
			$names = array('cities15000');
		}

		$fetchOnly = $this->input->getOption('fetch-only');
		$wipeFiles = $this->input->getOption('wipe-files');

		$this->setNames($names);

		// path to download our files
		$path = $this->getPath();

		// if we forced to wipe files, we will delete the directory
		$wipeFiles and $this->filesystem->deleteDirectory($path);

		// create the directory if it doesn't exists
		if ( ! $this->filesystem->isDirectory($path)) {
			$this->filesystem->makeDirectory($path, 0755, true);
		}

		$files = $this->getFiles();

		// loop all the files that we need to donwload
		foreach ($files as $file) {
			$filename = basename($file);

			if ($this->fileExists($path, $filename)) {
				$this->line("<info>File exists:</info> $filename");

				continue;
			}

			$this->line("<info>Downloading:</info> $file");

			$this->downloadFile($file, $path, $filename);

			// if the file is ends with zip, we will try to unzip it and remove the zip file
			if (substr($filename, -strlen('zip')) === "zip") {
				$this->line("<info>Unzip:</info> $filename");
				$filename = $this->extractZip($path, $filename);
			}
		}

		// if we only want to fetch files, we must stop the execution of the command
		if ($fetchOnly) {
			$this->line('<info>Files fetched.</info>');
			return;
		}

		$this->line("<info>It is time to seed the database. This may take 'a while'...</info>");

		// finally seed the common seeders
		$this->seedCommand('ContinentsTableSeeder');
		$this->seedCommand('CountriesTableSeeder');
		$this->seedCommand('AdminDivionsTableSeeder');
		$this->seedCommand('AdminSubdivionsTableSeeder');
		$this->seedCommand('HierarchiesTableSeeder');
		$this->seedCommand('FeaturesTableSeeder');
		$this->seedCommand('TimezonesTableSeeder');

		// TODO Specify an option.
//		$this->seedCommand('AlternateNamesTableSeeder');
		$this->seedCommand('LanguageCodesTableSeeder');

		foreach ($names as $nameFile) {
			$this->seedCommand('NamesTableSeeder', '--file=' . $nameFile);
		}
	}

	/**
	 * Download a file from a remote URL to a given path.
	 *
	 * @param  string  $url
	 * @param  string  $path
	 * @return void
	 */
	protected function downloadFile($url, $path, $filename)
	{
		if ( ! $fp = fopen ($path . '/' . $filename, 'w+')) {
			throw new RuntimeException('Impossible to write to path: ' . $path);
		}

		// It's shit. Guzzle should be used instead.
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}

	/**
	 * Given a zip archive, extract a the file and remove the original.
	 *
	 * @param  string  $path
	 * @param  string  $filename
	 * @return string
	 */
	protected function extractZip($path, $filename)
	{
		$this->archive->open($path . '/' . $filename);

		$this->archive->extractTo($path . '/');
		$this->archive->close();

		$this->filesystem->delete($path . '/' . $filename);

		return str_replace('.zip', '.txt', $filename);
	}

	/**
	 * Checks if a file already exists on a path. If the file contains .zip in
	 * the name we will also check for matches with .txt.
	 *
	 * @param  string  $path
	 * @param  string  $filename
	 * @return bool
	 */
	protected function fileExists($path, $filename)
	{
		if (file_exists($path . '/' . $filename)) {
			return true;
		}

		if (file_exists($path . '/' . str_replace('.zip', '.txt', $filename))) {
			return true;
		}

		return false;
	}

	/**
	 * Run a seed coman in a separate process.
	 *
	 * @param  string  $class
	 * @return void
	 */
	protected function seedCommand($class, $extra = '')
	{
		$string = 'php artisan geonames:seed --force --class="Ipalaus\Geonames\Seeders\%s" --path="%s" ' . $extra;

		$command = sprintf($string, $class, $this->getPath());

		$process = $this->makeProcess($command);

		$this->runProcess($process);

		$this->line("<info>Seeded:</info> $class");
	}

	/**
	 * Create a process with the given command.
	 *
	 * @param  string  $command
	 * @return \Symfony\Component\Process\Process
	 */
	protected function makeProcess($command)
	{
		return new Process($command, $this->laravel['path.base'], null, null, 0);
	}

	/**
	 * Run a given process.
	 *
	 * @param  \Symfony\Component\Process\Process $process
	 * @return \Symfony\Component\Process\Process
	 */
	public function runProcess(Process $process)
	{
		$process->run();

		if ( ! $process->isSuccessful()) {
			throw new \RuntimeException($process->getOutput());
		}

		return $process;
	}

	/**
	 * Return the working path.
	 *
	 * @return string
	 */
	protected function getPath()
	{
		return $this->config['path'];
	}

	/**
	 * Get the files to download.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return $this->config['files'];
	}

	protected function setNames($names)
	{
		foreach ($names as $name) {
			$this->config['files'][] = sprintf($this->config['wildcard'], $name);
		}
	}

	public function getArguments()
	{
		return array(
			array('names', InputArgument::IS_ARRAY, "'geoname' data files, like: RU, cities1000,.. (you can specify many)."),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('fetch-only', null, InputOption::VALUE_NONE, 'Just download the files.'),
			array('wipe-files', null, InputOption::VALUE_NONE, 'Wipe old downloaded files and fetch new ones.'),
		);
	}

}

