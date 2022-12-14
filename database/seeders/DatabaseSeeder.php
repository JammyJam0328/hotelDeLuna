<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\StayingHour;
use Illuminate\Database\Seeder;
use Database\Seeders\RateSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\BranchSeeder;
use Database\Seeders\AccountSeeder;
use Database\Seeders\RoomTypeSeeder;
use Database\Seeders\RoomStatusSeeder;
use Database\Seeders\DummyCheckInSeeder;
use Database\Seeders\FloorAndRoomSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Database\Seeders\AlmaResidencesDataSeeder;
use Database\Seeders\OverdueSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BranchSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(RoomStatusSeeder::class);
        $this->call(TransactionTypeSeeder::class);
        $this->call(RoomTypeSeeder::class);
        $this->call(RateSeeder::class);
        $this->call(AlmaResidencesDataSeeder::class);
        // $this->call(OverdueSeeder::class);
        StayingHour::create([
            'branch_id' => 1,
            'number' => '6',
        ]);
        StayingHour::create([
            'branch_id' => 1,
            'number' => '12',
        ]);

        StayingHour::create([
            'branch_id' => 1,
            'number' => '24',
        ]);
        
        // if (app()->environment() == 'local') {
        //     $this->call(FloorAndRoomSeeder::class);
        //     $this->call(DummyCheckInSeeder::class);
        // }
    }
}
