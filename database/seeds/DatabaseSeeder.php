<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('assets')->delete();
        \DB::table('users')->delete();
        \DB::table('command_center_shifts')->delete();

        $this->call([
            AssetSeeder::class,
            UserSeeder::class,
            CommandCenterShiftSeeder::class
        ]);
    }
}
