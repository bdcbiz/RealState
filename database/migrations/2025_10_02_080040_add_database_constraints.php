<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add validation constraints to users table
        DB::statement('ALTER TABLE users ADD CONSTRAINT check_role CHECK (role IN ("buyer", "company", "admin"))');

        // Add validation constraints to units table
        DB::statement('ALTER TABLE units ADD CONSTRAINT check_prices CHECK (normal_price >= 0)');
        DB::statement('ALTER TABLE units ADD CONSTRAINT check_areas CHECK (garden_area >= 0 AND roof_area >= 0)');
        DB::statement('ALTER TABLE units ADD CONSTRAINT check_floor CHECK (floor_number >= 0)');
        DB::statement('ALTER TABLE units ADD CONSTRAINT check_beds CHECK (number_of_beds >= 0)');

        // Add validation constraints to compounds table
        DB::statement('ALTER TABLE compounds ADD CONSTRAINT check_compound_areas CHECK (built_up_area >= 0 AND land_area >= 0 AND built_area >= 0)');
        DB::statement('ALTER TABLE compounds ADD CONSTRAINT check_floors CHECK (how_many_floors >= 0)');
        DB::statement('ALTER TABLE compounds ADD CONSTRAINT check_progress CHECK (completion_progress BETWEEN 0 AND 100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT check_role');
        DB::statement('ALTER TABLE units DROP CONSTRAINT check_prices');
        DB::statement('ALTER TABLE units DROP CONSTRAINT check_areas');
        DB::statement('ALTER TABLE units DROP CONSTRAINT check_floor');
        DB::statement('ALTER TABLE units DROP CONSTRAINT check_beds');
        DB::statement('ALTER TABLE compounds DROP CONSTRAINT check_compound_areas');
        DB::statement('ALTER TABLE compounds DROP CONSTRAINT check_floors');
        DB::statement('ALTER TABLE compounds DROP CONSTRAINT check_progress');
    }
};
