<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Guest;
use App\Models\Deposit;
use Illuminate\Database\Seeder;

class DummyCheckInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i < 20; $i++) {
            $guest = Guest::create([
                'branch_id' => 1,
                'qr_code' => 'qr_code_' . $i,
                'name' => fake()->name,
                'contact_number' => fake()->phoneNumber,
                'is_out_of_the_building' => false,
            ]);
            
            $transaction = $guest->transactions()->create([
                'branch_id' => 1,
                'transaction_type_id' => 1,
                'payable_amount' => '200',
                'room_id' => $i,
            ]);

            $check_in_detail = $transaction->check_in_detail()->create([
                'room_id' => $i,
                'rate_id' => 1,
                'static_amount' => '200',
                'static_hours_stayed' => '6',
            ]);
            $deposite = $guest->transactions()->create([
                'branch_id' => 1,
                'transaction_type_id' => 2,
                'payable_amount' => '200',
                'room_id' => $i,
            ]);
            Deposit::create([
                'transaction_id' => $deposite->id,
                'amount' => 200,
                'remarks' => 'Deposit for remote and key',
            ]);
            $selected_room = Room::find($check_in_detail->room_id);
            $selected_room->update([
                'room_status_id' => 6,
                'priority' => false,
            ]);
        }
    }
}
