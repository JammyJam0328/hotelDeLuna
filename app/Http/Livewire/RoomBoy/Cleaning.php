<?php

namespace App\Http\Livewire\RoomBoy;

use App\Models\Cleaning as CleaningModel;
use App\Models\Designation;
use App\Models\Room;
use Carbon\Carbon;
use Livewire\Component;
use WireUi\Traits\Actions;
use Livewire\WithFileUploads;

class Cleaning extends Component
{
    use Actions;
    use WithFileUploads;

    public $current_assigned_floor;
    public $filter = 'ASC';
    public $shift;
    public $room;
    public $photo;
    public $show_designation_only = true;
    public $cleaned_rooms = false;
    public $date_from;
    public $date_to;

    public function updatedShift()
    {
        if ($this->shift == 1) {
            $this->datefrom = Carbon::now()->format('Y-m-d') . ' 08:01:00';
            $this->dateto = Carbon::now()->format('Y-m-d') . ' 20:00:00';
        } else {
            $this->datefrom = Carbon::now()->format('Y-m-d') . ' 20:01:00';
            $this->dateto =
                Carbon::now()
                    ->addDay()
                    ->format('Y-m-d') . ' 08:00:00';
        }
    }

    public function getDesignationProperty()
    {
        return Designation::query()
            ->where('room_boy_id', auth()->user()->room_boy->id)
            ->where('current', 1)
            ->with(['floor'])
            ->first();
    }

    public function startRoomCleaning($room_id)
    {
        $query = Room::where('id', $room_id)->first();
        if ($query->room_status_id == 8) {
            $this->dialog()->error(
                $title = 'Sorry',
                $description = 'This room is already in cleaning process.'
            );
        } else {
            if (auth()->user()->room_boy->is_cleaning) {
                $this->dialog()->error(
                    $title = 'Sorry',
                    $description =
                        'You are not able to clean this room. please make sure you dont have pending unclean room.'
                );
                return;
            }
            $this->dialog()->confirm([
                'title' => 'Are you Sure?',
                'description' => 'Do you want to continue this action?',
                'icon' => 'question',
                'accept' => [
                    'label' => 'Yes, save it',
                    'method' => 'confirmStartRoomCleaning',
                    'params' => $room_id,
                ],
                'reject' => [
                    'label' => 'No, cancel',
                ],
            ]);
        }
    }

    public function confirmStartRoomCleaning($room_id)
    {
        $room = Room::where('id', $room_id)->first();
        if ($room->room_status_id == 8) {
            $this->dialog()->error(
                $title = 'Sorry',
                $description = 'This room is already in cleaning process.'
            );
            return;
        }
        $room->update([
            'room_status_id' => 8,
        ]);
        CleaningModel::create([
            'room_boy_id' => auth()->user()->room_boy->id,
            'room_id' => $room->id,
            'suppose_to_start' => $room->updated_at,
            'suppose_to_end' => $room->time_to_clean,
            'started_at' => Carbon::now(),
        ]);
        auth()
            ->user()
            ->room_boy->update([
                'is_cleaning' => 1,
                'room_id' => $room_id,
            ]);
    }

    public function finish($room_id)
    {
        $this->dialog()->confirm([
            'title' => 'Are you Sure?',
            'description' => 'Do you want to continue this action?',
            'icon' => 'question',
            'accept' => [
                'label' => 'Yes, save it',
                'method' => 'confirmFinish',
                'params' => $room_id,
            ],
            'reject' => [
                'label' => 'No, cancel',
            ],
        ]);
    }

    public function confirmFinish($room_id)
    {
        $room = Room::where('id', $room_id)->first();
        if (
            $room->room_status_id == 8 &&
            $room->updated_at->diffInMinutes(Carbon::now()) < 15
        ) {
            $this->notification()->error(
                $title = 'Error',
                $description = 'You can not finish this room before 15 minutes'
            );
            return;
        }
        $delayed = $room->time_to_clean < Carbon::now();

        $query = Room::whereHas('floor', function ($q) {
            $q->where('branch_id', auth()->user()->branch_id);
        })
            ->where('room_status_id', 1)
            ->where('priority', 1)
            ->count();

        if ($query < 10) {
            $room->update([
                'room_status_id' => 1,
                'time_to_clean' => null,
                'priority' => true,
            ]);
        } else {
            $room->update([
                'room_status_id' => 9,
                'time_to_clean' => null,
                'priority' => false,
            ]);
        }

        $cleaning = CleaningModel::where('room_id', $room_id)
            ->where('finish_at', null)
            ->first();

        $cleaning->update([
            'finish_at' => Carbon::now(),
            'delayed' => $delayed,
        ]);
        auth()
            ->user()
            ->room_boy->update([
                'is_cleaning' => 0,
                'room_id' => null,
            ]);
        $this->notification()->success(
            $title = 'Finish',
            $description = 'Room is now ready to use'
        );
    }

    public function test()
    {
        dd('sdsdsd');
    }

    public function render()
    {
        return view('livewire.room-boy.cleaning', [
            'rooms' => $this->designation
                ? Room::query()
                    ->when($this->show_designation_only == true, function (
                        $query
                    ) {
                        $query->where('floor_id', $this->designation->floor_id);
                    })
                    ->whereIn('room_status_id', [7, 8])
                    ->orderBy('updated_at', 'ASC')
                    ->get()
                : [],
            'history' => CleaningModel::query()
                ->where('room_boy_id', auth()->user()->room_boy->id)
                ->orderBy('id', $this->filter)
                ->get(),

            'cleans' => $this->getGeneratedQuery(),
        ]);
    }

    public function getGeneratedQuery()
    {
        if ($this->shift == 1) {
            return CleaningModel::query()
                ->where('room_boy_id', auth()->user()->room_boy->id)
                ->where('created_at', '>=', $this->datefrom)
                ->where('created_at', '<=', $this->dateto)
                ->get();
        } elseif ($this->shift == 2) {
            return CleaningModel::query()
                ->where('room_boy_id', auth()->user()->room_boy->id)
                ->where('created_at', '>=', $this->datefrom)
                ->where('created_at', '<=', $this->dateto)
                ->get();
        } else {
            return CleaningModel::query()
                ->where('room_boy_id', auth()->user()->room_boy->id)
                ->get();
        }
    }
}
