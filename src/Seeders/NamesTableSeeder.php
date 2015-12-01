<?php namespace Ipalaus\Geonames\Seeders;

class NamesTableSeeder extends DatabaseSeeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$path = $this->command->option('path');
		$file = $this->command->option('file');

		$this->importer->names('geonames_names', $path . '/' . $file . '.txt');
	}

}
