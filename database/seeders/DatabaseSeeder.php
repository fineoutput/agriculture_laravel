<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
          // Insert initial data into the 'admin_sidebar' table
          DB::table('admin_sidebar')->insert([
            'name' => 'Dashboard',
            'url' => 'admin_index',
            'icon' => 'fa fa-bar-chart',
            'seq' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admin_sidebar')->insert([
            'name' => 'Team',
            'url' => '#',
            'icon' => 'fas fa-users-cog',
            'seq' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admin_sidebar2')->insert([
            'main_id' => '2',
            'name' => 'View Team',
            'url' => 'view_team',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admin_sidebar2')->insert([
            'main_id' => '2',
            'name' => 'Add Team',
            'url' => 'add_team_view',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admin_teams')->insert([
            'name' => 'demo',
            'email' => 'demo@gmail.com',
            'password' => '$2a$12$GmX16nb2xd/7.mqbVqcmlugTK6u0nx8dXYbEnHd2e.8qh29Eu3LL.',
            'phone' => '9799655891',
            'address' => '19 kalyanpuri new sanganer road',
            'image' => '',
            'power' => '1',
            'services' => '["999"]',
            'ip' => '183.83.42.146',
            'added_by' => '1',
            'is_active' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('crm_settings')->insert([

            'sitename' => 'fineoutput.com',
            'instagram_link' => 'https://www.instagram.com/',
            'youtube_link' => 'https://www.youtube.com/',
            'facebook_link' => 'https://www.facebook.com/',
            'phone' => '6352418574',
            'address' => 'Jaipur',
            'ip' => '192.168.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
