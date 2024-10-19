<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $path = database_path('seeders/sql/users.sql');

        if (File::exists($path)) {
            $sql = File::get($path);

            DB::unprepared($sql);
        } else {
            $this->command->error("File not found: {$path}");
        }
    }
}
