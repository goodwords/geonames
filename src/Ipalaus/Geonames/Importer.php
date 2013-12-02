<?php namespace Ipalaus\Geonames;

use Clousure;
use RuntimeException;
use Illuminate\Filesystem\Filesystem;

class Importer {

	/**
	 * Repository implementation.
	 *
	 * @var \Ipalaus\Geonames\RepositoryInterface
	 */
	protected $repository;

	/**
	 * Filesystem implementation.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * Create a new instance of Importer.
	 *
	 * @param  \Ipalaus\Geonames\RepositoryInterface  $repository
	 * @param  \Illuminate\Filesystem\Filesystem              $filesystem
	 * @return void
	 */
	public function __construct(RepositoryInterface $repository, Filesystem $filesystem)
	{
		$this->repository = $repository;
		$this->filesystem = $filesystem;
	}

	/**
	 * Parse the names file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function names($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'id'              => $row[0],
				'name'            => $row[1],
				'ascii_name'      => $row[2],
				'alternate_names' => $row[3],
				'latitude'        => $row[4],
				'longitude'       => $row[5],
				'f_class'         => $row[6],
				'f_code'          => $row[7],
				'country_id'      => $row[8],
				'cc2'             => $row[9],
				'admin1'          => $row[10],
				'admin2'          => $row[11],
				'admin3'          => $row[12],
				'admin4'          => $row[13],
				'population'      => $row[14],
				'elevation'       => $row[15],
				'gtopo30'         => $row[16],
				'timezone_id'     => $row[17],
				'modification_at' => $row[18],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Parse the countries file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function countries($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'iso_alpha2'           => $row[0],
				'iso_alpha3'           => $row[1],
				'iso_numeric'          => $row[2],
				'fips_code'            => $row[3],
				'name'                 => $row[4],
				'capital'              => $row[5],
				'area'                 => $row[6],
				'population'           => $row[7],
				'continent'            => $row[8],
				'tld'                  => $row[9],
				'currency'             => $row[10],
				'currency_name'        => $row[11],
				'phone'                => $row[12],
				'postal_code_format'   => $row[13],
				'postal_code_regex'    => $row[14],
				'name_id'              => $row[15],
				'languages'            => $row[16],
				'neighbours'           => $row[17],
				'equivalent_fips_code' => $row[18],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Inserts the continents to the database.
	 *
	 * @param  string  $table
	 * @param  array   $continents
	 * @return void
	 */
	public function continents($table, $continents)
	{
		$this->isEmpty($table);

		foreach ($continents as $continent) {
			$insert = array(
				'iso_alpha2'           => $row[0],
				'iso_alpha3'           => $row[1],
				'iso_numeric'          => $row[2],
				'fips_code'            => $row[3],
				'name'                 => $row[4],
				'capital'              => $row[5],
				'area'                 => $row[6],
				'population'           => $row[7],
				'continent'            => $row[8],
				'tld'                  => $row[9],
				'currency'             => $row[10],
				'currency_name'        => $row[11],
				'phone'                => $row[12],
				'postal_code_format'   => $row[13],
				'postal_code_regex'    => $row[14],
				'name_id'              => $row[15],
				'languages'            => $row[16],
				'neighbours'           => $row[17],
				'equivalent_fips_code' => $row[18],
			);

			$this->repository->insert($table, $insert);
		}
	}

	/**
	 * Parse the language codes file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function languageCodes($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'iso_639_3'     => $row[0],
				'iso_639_2'     => $row[1],
				'iso_639_1'     => $row[2],
				'language_name' => $row[3],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Parse the admin divisions and subdivisions file and inserts it to the
	 * database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function adminDivions($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'code'       => $row[0],
				'name'       => $row[1],
				'name_ascii' => $row[2],
				'name_id'    => $row[3],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Parse the hierachies file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function hierarchies($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'parent_id' => $row[0],
				'child_id'  => $row[1],
				'type'      => $row[2],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Parse the features file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function features($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'code'        => $row[0],
				'name'        => $row[1],
				'description' => $row[2],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Parse the timezones file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function timezones($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'id'         => $row[0],
				'gmt_offset' => $row[1],
				'dst_offset' => $row[2],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Parse the alternate names file and inserts it to the database.
	 *
	 * @param  string  $table
	 * @param  string  $path
	 * @return void
	 */
	public function alternateNames($table, $path)
	{
		$this->isEmpty($table);

		$this->parseFile($path, function($row) use ($table)
		{
			$insert = array(
				'id'             => $row[1],
				'name_id'        => $row[2],
				'iso_language'   => $row[3],
				'alternate_name' => $row[4],
				'is_preferred'   => $row[5],
				'is_short'       => $row[6],
				'is_colloquial'  => $row[7],
				'is_historic'    => $row[8],
			);

			$this->repository->insert($table, $insert);
		});
	}

	/**
	 * Prevent wrong executons of the importer.
	 *
	 * @param  string   $table
	 * @return void
	 */
	protected function isEmpty($table)
	{
		if ( ! $this->repository->isEmpty($table)) {
			throw new RuntimeException("The table [$table] is not empty.");
		}
	}

	/**
	 * Parse a given file and return the CSV lines as an array.
	 *
	 * @param  string     $path
	 * @param  \Clousure  $callback
	 * @return void
	 */
	protected function parseFile($path, $callback)
	{
		$handle = fopen($path, 'r');

		if ( ! $handle) {
			throw new RuntimeException("Impossible to open file: $path");
		}

		// gets the lines and run the callback until we reach the end of file
		while ( ! feof($handle)) {
			$line = fgets($handle, 1024 * 32);

			// ignore empty lines and comments
			if ( ! $line or $line === '' or strpos($line, '#') === 0) continue;

			// our CSV is <TAB> separated so we only need to conver it to an array
			$line = explode("\t", $line);

			// finally run our clousure with the line
			$callback($line);
		}

		fclose($handle);
	}

}