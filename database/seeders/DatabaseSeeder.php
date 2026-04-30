<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AnnouncementSeeder::class,
            MajorSeeder::class,
            ScheduleSeeder::class,
            AdminUserSeeder::class,
            HistoricalDataMigrationSeeder::class,
            RegistrationDocumentSeeder::class,
        ]);
    }
}
