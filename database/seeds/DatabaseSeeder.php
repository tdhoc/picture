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
        DB::table('users')->insert([
            'username' => "user",
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'type' => 'user',
        ]);
        DB::table('users')->insert([
            'username' => "admin",
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'type' => 'admin',
        ]);
        DB::table('category')->insert([
            'name' => "category",
        ]);
        DB::table('subcategory')->insert([
            'name' => "subcategory",
            'category_id' => "1",
        ]);
        // $this->call(UserSeeder::class);
    }
}
