<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create N users and N groups
        // create records in pivot table: one user will be associated with one group
        factory(App\User::class, 5)->create()->each(function ($user) {
            $user->groups()->save(factory(App\Group::class)->make());
        });
    }
}
