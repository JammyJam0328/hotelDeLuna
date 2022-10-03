<?php

namespace App\Http\Livewire\Kiosk;

use App\Jobs\TerminateRoomJob;
use App\Models\CheckInDetail;
use App\Models\Guest;
use App\Models\Rate;
use App\Models\Room;
use App\Models\TemporaryRoom;
use App\Models\Transaction;
use App\Models\Type;
use Carbon\Carbon;
use Livewire\Component;
use WireUi\Traits\Actions;
use App\Models\Floor;
use Livewire\WithPagination;

class Checkin extends Component
{
    use Actions;
    use WithPagination;
    public $step = 1;
    public $increments = '';

    public $get_room = [
        'room_id' => '',
        'type_id' => '',
        'rate_id' => '',
    ];

    public $transaction = [];

    public $room_array = 0;

    public $manage_room;

    public $floor_id = 1;

    public $type_key;

    public $room_type;

    public $customer_name;

    public $customer_number;

    public $qr_code;

    public $manageRoomPanel = false;

    public $temporary = [];

    public function render()
    {

        return view('livewire.kiosk.checkin', [
            'rooms' => Room::where('room_status_id', 1)->where('floor_id', 'like', '%' . $this->floor_id . '%')->where('type_id', $this->type_key)->whereHas('floor', function ($query) {
                $query->where('branch_id', auth()->user()->branch_id);
            })->orderBy('updated_at','DESC')->with(['floor'])->take(10)->get(),

            'floors' => Floor::where('branch_id', auth()->user()->branch_id)->get(),
            'roomtypes' => Type::get(),
            'rates' => Rate::where('type_id', 'like', '%' . $this->type_key . '%')->with(['staying_hour','type'])->get(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'customer_name' => 'required|min:3',
            'customer_number' => 'required|numeric|digits:9',
        ]);
    }

    public function closeManageRoomPanel()
    {
        $this->dialog()->confirm([

            'title'       => 'Room Selection Management',

            'description' => 'Are you sure you want to cancel this transaction?',

            'icon'        => 'question',

            'accept'      => [

                'label'  => 'Yes',

                'method' => 'cancelRoomSelection',

            ],

            'reject' => [

                'label'  => 'No, cancel',

            ],

        ]);
    }

    public function cancelRoomSelection()
    {
        $query =  Room::where('id', $this->get_room['room_id'])->first();
        $query->update([
            'room_status_id' => 1,
        ]);
        $this->manageRoomPanel = false; 
        $this->notification()->success(
            $title = 'Kiosk Check-In',
            $description = 'Cancel Room Successfully.',
        );
    }

    public function selectRoom($room_id)
    {
        $query = Room::where('id', $room_id)->first();
        if ($query->room_status_id != 1) {
            $this->notification()->error(
                $title = 'Kiosk Check-In',
                $description = 'The Room is already selected by other user',
            );
        } else {
            $query->update([
                'room_status_id' => 6,
            ]);
            $this->get_room['room_id'] = $room_id;
            $this->manageRoomPanel = true;
        }
    }

    public function selectRoomType($type_id)
    {   
        // $this->floor_id = 1;
      
      
       
        $this->get_room['type_id'] = $type_id;
        $this->room_array++;
        $this->type_key = $type_id;
        $query = Room::where('type_id', $type_id)->where('room_status_id', 1)->whereHas('floor', function ($query) {
            $query->where('branch_id', auth()->user()->branch_id);
        })->with('floor')->first()->floor_id;
        $this->floor_id = $query;
    }

    public function manageRoom($key)
    {
        $this->manage_room = $this->transaction[$key]['room_id'];
        $this->room_key = $key;
    }

    public function removeRoom($key)
    {
        unset($this->transaction[$key]);
        $this->room_array--;
    }

    public function selectType($type_id)
    {
        $rate = Rate::where('type_id', $type_id)->first();
        // $this->transaction[$this->room_key]['rate_id'] = $rate->id;
        $this->transaction[$this->room_key]['type_id'] = $type_id;
        $this->room_type = Rate::where('type_id', $type_id)->get();
    }

    public function selectRate($rate_id)
    {
        $this->get_room['rate_id'] = $rate_id;
    }

    public function confirmCheckin()
   
    {
        // dd('sdsdsdsdsd');   
        $this->validate([
            'customer_name' => 'required|min:3',
            'customer_number' => 'nullable|digits:9',
        ]);
        $transaction = \App\Models\Guest::whereYear('created_at', \Carbon\Carbon::today()->year)->count();
        $transaction += 1;
        $transaction_code = auth()->user()->branch_id . today()->format('y') . str_pad($transaction, 4, '0', STR_PAD_LEFT);



        $guest = Guest::create([
            'branch_id' => auth()->user()->branch_id,
            'qr_code' => $transaction_code,
            'name' => $this->customer_name,
            'contact_number' => '09'.$this->customer_number,
        ]);

        $room = Room::where('id', $this->get_room['room_id'])->first();
        $rate = Rate::where('id', $this->get_room['rate_id'])->first();

        $checkinroom = Transaction::create([
            'branch_id' => auth()->user()->branch_id,
            'guest_id' => $guest->id,
            'transaction_type_id' => 1,
            'payable_amount' => $rate->amount,
        ]);
        $checkindeposit = Transaction::create([
            'branch_id' => auth()->user()->branch_id,
            'guest_id' => $guest->id,
            'transaction_type_id' => 2,
            'payable_amount' => 200,
        ]);

        $details = CheckInDetail::create([
            'transaction_id' => $checkinroom->id,
            'room_id' => $this->get_room['room_id'],
            'rate_id' => $this->get_room['rate_id'],
            'static_amount' => $rate->amount,
            'static_hours_stayed' => $rate->staying_hour->number,
        ]);

        $room->update([
            'room_status_id' => 6,
            'time_to_terminate_in_queue' => Carbon::now()->addMinutes(10),
        ]);

        $this->qr_code = $transaction_code;
        $this->step = 4;
        
        $time_to_terminate = 2;
        TerminateRoomJob::dispatch($room->id,$guest->id)->delay(now()->addHours($time_to_terminate));
    }

    public function confirmRate()
    {
        $this->validate([
            'get_room.rate_id' => 'required',
        ]);
        $this->manageRoomPanel = false;
        $this->step = 3;
    }
}
