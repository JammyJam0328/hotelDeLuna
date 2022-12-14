<?php

namespace App\Http\Livewire\FrontDesk;

use App\Models\CheckInDetail;
use App\Models\Deposit;
use App\Models\Discount;
use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomTransactionLog;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Psy\Readline\Transient;
use Termwind\Components\Dd;
use WireUi\Traits\Actions;
use App\Traits\WithCaching;

class CheckIn extends Component
{
    use WithPagination, Actions, WithCaching;

    public $search;

    public $searchBy = '1';

    public $realSearch;

    public $excess_amount;

    public $given_amount;

    public $save_as_deposit = false;

    protected $listeners = ['refreshRecentCheckInList' => 'clearSearches'];

    public $showModal = false;

    public $guest = null;

    public $showDiscountsList = false;

    public $selectedDiscounts = [];

    public $discounted_amount = 0;

    public $total_amount;

    public $recent_check_in_order = 'DESC';

    public $discounts = [];

    public function updatedGivenAmount()
    {
        $this->useCacheRows();
        if ($this->given_amount) {
            $this->excess_amount = $this->given_amount - $this->total_amount;
        } else {
            $this->excess_amount = 0;
        }
    }
    public function toggle_recent_check_in_order()
    {
        if ($this->recent_check_in_order == 'DESC') {
            $this->recent_check_in_order = 'ASC';
        } else {
            $this->recent_check_in_order = 'DESC';
        }
    }

    public function searchReal()
    {
        $this->realSearch = $this->search;
        $this->reset('search');
    }

    public function clearSearches()
    {
        $this->realSearch = '';
        $this->search = '';
        $this->searchBy = '1';
    }


    public function setGuest($guest_id)
    {
        $this->useCacheRows();
        $this->reset('excess_amount', 'given_amount', 'save_as_deposit');
        $this->guest  = Guest::where('id', $guest_id)
            ->with([
                'checkInDetail',
                'transactions'
            ])->first();
        if ($this->guest->terminated_at != null) {
            $this->notification()->error(
                $title = 'Error',
                $description = 'Guest failed to check in within 2 hours. As per policy, guest is terminated.',
            );
            return;
        }
        $this->total_amount = $this->guest->transactions->sum('payable_amount');
        $this->showModal = true;
    }

    public function confirmCheckIn()
    {
        $this->useCacheRows();
        $this->dialog()->confirm([
            'title' => 'Are you Sure?',
            'description' => 'Are you sure you want to check in this guest?',
            'icon' => 'question',
            'accept' => [
                'label' => 'Yes, Check In',
                'method' => 'checkIn',
            ],
            'reject' => [
                'label' => 'No, cancel',
            ],
        ]);
    }

    public function checkIn()
    {
        $this->validate([
            'given_amount' => 'required|numeric|min:0|min:' . $this->total_amount,
        ]);
        DB::beginTransaction();

        $check_in_transaction = $this->guest->transactions()->where('transaction_type_id', 1)->first();
        $default_deposite = $this->guest->transactions()->where('transaction_type_id', 2)->first();

        $check_in_transaction->update([
            'paid_amount' => $this->given_amount - 200,  // 200 pesos will be added to remote and key deposite
            'change_amount' => $this->excess_amount,
            'paid_at' => Carbon::now(),
            'assigned_frontdesks'=>auth()->user()->assigned_frontdesks,
        ]);

        $check_in_detail = $this->guest->checkInDetail;

        $check_in_detail->update([
            'check_in_at' => Carbon::now(),
            'expected_check_out_at' => Carbon::now()->addHours($check_in_detail->static_hours_stayed),
        ]);

        Room::where('id', $check_in_detail->room_id)->update([
            'room_status_id' => 2,
        ]);

        $default_deposite->update([
            'paid_amount' => 200,
            'change_amount' => 0,
            'paid_at' => Carbon::now(),
            'assigned_frontdesks'=>auth()->user()->assigned_frontdesks,
        ]);

        if ($this->save_as_deposit) {
            $this->guest->transactions()->create([
                'room_id' => $check_in_detail->room_id,
                'branch_id' => auth()->user()->branch_id,
                'transaction_type_id' => 2,
                'payable_amount' => $this->excess_amount,
                'paid_amount' => $this->excess_amount,
                'change_amount' => 0,
                'paid_at' => Carbon::now(),
                'remarks' => 'Excess amount from check in',
                'assigned_frontdesks'=>auth()->user()->assigned_frontdesks,
            ]);
            $ids = json_decode(auth()->user()->assigned_frontdesks);
            $frontdesks = Frontdesk::whereIn('id',$ids)->get();
            Deposit::create([
                'guest_id' => $this->guest->id,
                'amount' => $this->excess_amount,
                'remarks' => 'Excess amount from check in',
                'front_desk_names' => $frontdesks->pluck('name')->implode(' and '),
            ]);
        }

        $this->guest->update([
            'is_checked_in' => true,
            'check_in_at' => Carbon::now(),
        ]);

        RoomTransactionLog::create([
            'branch_id' => auth()->user()->branch_id,
            'room_id' =>$check_in_detail->room_id,
            'room_number' => $check_in_detail->room->number,
            'check_in_detail_id' => $check_in_detail->id,
            'check_in_at' => $check_in_detail->check_in_at,
            'time_interval' => $check_in_detail->room->last_check_out_at ? $check_in_detail->check_in_at->diffInMinutes($check_in_detail->room->last_check_out_at) : 0,
        ]);

        DB::commit();
        $this->showModal = false;
        $this->notification()->success(
            $title = 'Success!',
            $description = 'Guest has been checked in.',
        );
    }

    public function selectDiscount($id, $amount, $is_percentage)
    {
        $array = [
            'id' => $id,
            'amount' => $amount,
            'is_percentage' => $is_percentage,
        ];

        if (in_array($array, $this->selectedDiscounts)) {
            $this->selectedDiscounts = array_filter($this->selectedDiscounts, function ($item) use ($id) {
                return $item['id'] != $id;
            });
            if ($is_percentage) {
                $amount_payable = Transaction::where('guest_id', $this->guest->id)->where('transaction_type_id', 1)->first()->payable_amount;
                $amount = ($amount_payable * $amount) / 100;
                $this->discounted_amount -= $amount;
            } else {
                $this->discounted_amount -= $amount;
            }
        } else {
            array_push($this->selectedDiscounts, $array);
            if ($is_percentage) {
                $amount_payable = Transaction::where('guest_id', $this->guest->id)->where('transaction_type_id', 1)->first()->payable_amount;
                $amount = ($amount_payable * $amount) / 100;
                $this->discounted_amount += $amount;
            } else {
                $this->discounted_amount += $amount;
            }
        }
    }

    public function searchByQrCode()
    {
        if ($this->search) {
            $guest = Guest::where('qr_code', $this->search)
                ->where('terminated_at', null)
                ->where('is_checked_in', false)
                ->where('branch_id', auth()->user()->branch->id)
                ->first();
            if (!$guest) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'QR Code not found'
                );
                $this->guest = null;
                $this->search = '';
                return;
            }
            return $guest;
        }
    }

    public function searchByRoomNumber()
    {
        if ($this->search) {
            $room = Room::where('number', $this->search)
                ->whereHas('floor', function ($query) {
                    $query->where('branch_id', auth()->user()->branch_id);
                })
                ->where('room_status_id', 6)
                ->first();
            if (!$room) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Room not exist in the queue'
                );
                $this->search = '';
                return;
            }
            $check_in_detail = CheckInDetail::where('room_id', $room->id)
                ->where('check_in_at', null)
                ->where('check_out_at', null)
                ->latest()
                ->first();
            if (!$check_in_detail) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Guest not found or already checked out.'
                );
                $this->search = '';
                return;
            }

            if ($check_in_detail->transaction->guest->terminated_at != null || $check_in_detail->transaction->guest->is_checked_in != false) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Guest not found'
                );
                $this->search = '';
                return;
            }
            return $check_in_detail->transaction->guest;
        }
    }

    public function searchByName()
    {
        if ($this->search) {
            $guest = Guest::where('name', $this->search)
                ->where('terminated_at', null)
                ->where('is_checked_in', false)
                ->where('branch_id', auth()->user()->branch->id)
                ->paginate(10);
            if (!$guest) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Guest not found or already checked out.'
                );
                $this->search = '';
                return;
            }
            return $guest;
        }
    }
    public function runQuery()
    {
        if ($this->realSearch != '') {
            switch ($this->searchBy) {
                case '1':
                    return $this->searchByQrCode();
                    break;
                case '2':
                    return $this->searchByName();
                    break;
                case '3':
                    return $this->searchByRoomNumber();
                    break;
            }
        } else {
            return  Guest::query()
                ->where('terminated_at', null)
                ->where('is_checked_in', false)
                ->paginate(9);
        }
    }

    public function getGuestsQueryProperty()
    {
        return Guest::where('branch_id', auth()->user()->branch->id)
            ->where('terminated_at', null)
            ->where('is_checked_in', false)
            ->with(['checkInDetail','transactions']);
    }

    public function getGuestsProperty()
    {
        if ($this->realSearch != '') {
            switch ($this->searchBy) {
                case '1':
                    $this->guestsQuery->where('qr_code', $this->realSearch);
                    break;
                case '2':
                    $this->guestsQuery->where('name', 'like', '%' . $this->realSearch . '%');
                    break;
                case '3':
                    $this->guestsQuery->whereHas('checkInDetail.room', function ($query) {
                            $query->where('number', $this->realSearch);
                      });
                    break;
            }
        }
        return $this->cache(function () {
            return $this->guestsQuery->paginate(9);
        }, 'guests');
    }

    public function mount()
    {
        $this->discounts = Discount::where('branch_id', auth()->user()->branch_id)
            ->where('is_available', true)
            ->get();
    }

    public function getRecentCheckInsQueryProperty()
    {
        return Guest::query()->where('is_checked_in', true)
                ->orderBy('check_in_at', 'desc')
                ->take(10);
    }

    public function getRecentCheckInsProperty()
    {
        return $this->cache(function () {
            return $this->recentCheckInsQuery->get();
        }, 'recentCheckIns');
    }

    public function render()
    {
        return view('livewire.front-desk.check-in', [
            'guests' => $this->guests,
            'transactions' => $this->showModal != false ?
                $this->guest->transactions()
                ->orderBy('created_at', 'desc')->get() : [],
            'recent_check_in_list' => $this->recentCheckIns,
        ]);
    }
}
