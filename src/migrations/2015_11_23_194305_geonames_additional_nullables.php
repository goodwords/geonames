<?php

use Illuminate\Database\Migrations\Migration;

class GeonamesAdditionalNullables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin1 VARCHAR(20) DEFAULT NULL');
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin2 VARCHAR(80) DEFAULT NULL');
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin3 VARCHAR(20) DEFAULT NULL');
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin4 VARCHAR(20) DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin1 VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin2 VARCHAR(80) NOT NULL');
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin3 VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE geonames_names MODIFY COLUMN admin4 VARCHAR(20) NOT NULL');
    }
}
