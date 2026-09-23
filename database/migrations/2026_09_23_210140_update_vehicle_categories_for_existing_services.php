<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET SESSION sql_mode = ""');

        // Bike services (8-9)
        DB::table('services')->where('id', 8)->update(['vehicle_category' => 'Bike']);
        DB::table('services')->where('id', 9)->update(['vehicle_category' => 'Bike']);

        // Three-wheeler services (10-17)
        DB::table('services')->whereIn('id', [10, 11, 12, 13, 14, 15, 16, 17])->update(['vehicle_category' => 'Three-wheeler']);

        // Small Car services (18-20)
        DB::table('services')->whereIn('id', [18, 19, 20])->update(['vehicle_category' => 'Small Car']);

        // Sedan services (21-23)
        DB::table('services')->whereIn('id', [21, 22, 23])->update(['vehicle_category' => 'Sedan']);

        // SUV services (24-27)
        DB::table('services')->whereIn('id', [24, 25, 26, 27])->update(['vehicle_category' => 'SUV']);

        // Bus services (28-40, 41) - excluding 32 (GREASING)
        DB::table('services')->whereIn('id', [28, 29, 30, 31, 33, 34, 35, 36, 37, 38, 39, 40, 41])->update(['vehicle_category' => 'Bus']);

        // Van services (42-47)
        DB::table('services')->whereIn('id', [42, 43, 44, 45, 46, 47])->update(['vehicle_category' => 'Van']);

        // Lorry services (48-63)
        DB::table('services')->whereIn('id', [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63])->update(['vehicle_category' => 'Lorry']);

        // Boom Truck (64)
        DB::table('services')->where('id', 64)->update(['vehicle_category' => 'Boom Truck']);

        // JCB Truck (65-66)
        DB::table('services')->whereIn('id', [65, 66])->update(['vehicle_category' => 'JCB Truck']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET SESSION sql_mode = ""');
        DB::table('services')->update(['vehicle_category' => null]);
    }
};
