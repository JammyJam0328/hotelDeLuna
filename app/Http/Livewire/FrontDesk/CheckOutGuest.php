<?php

namespace App\Http\Livewire\FrontDesk;

use App\Models\Room;
use App\Models\Guest;
use Livewire\Component;
use WireUi\Traits\Actions;
use App\Models\Transaction;
use App\Models\CheckInDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Termwind\Components\Dd;

class CheckOutGuest extends Component
{
    use Actions;

    public $override = [
        'modal' => false,
        'new_amount',
        'authorization_code',
    ];

    protected $validationAttributes = [
        'override.new_amount' => 'new amount',
        'override.authorization_code' => 'authorization code',
    ];

    public $overridable = null;

    public $guest = null;

    public $search = '';

    public $transactionOrder = 'DESC';

    public $final_reminder = false;

    public $searchBy = null;

    protected $queryString = ['search', 'searchBy'];

    public function showOverrideModal($transaction_id)
    {
        $this->overridable['authorization_code'] = "";
        $this->overridable = Transaction::find($transaction_id);
        $this->override['modal'] = true;
        $this->override['new_amount'] = $this->overridable->payable_amount;
    }

    public function overrideTransaction()
    {
        $authorization_code = auth()->user()->branch->authorization_code;
        $this->validate([
            'override.new_amount' => 'required|numeric',
            'override.authorization_code' => 'required|in:' . $authorization_code,
        ]);
        $this->overridable->update([
            'payable_amount' => $this->override['new_amount'],
            'override_at' => now()
        ]);
        $this->notification()->success(
            $title = 'Success',
            $description = 'Transaction has been overridden'
        );
        $this->override['modal'] = false;
        $this->overridable = null;
    }

    public function toogleTransactionOrder()
    {
        $this->transactionOrder = $this->transactionOrder == 'DESC' ? 'ASC' : 'DESC';
    }

    public function searchByQrCode()
    {
        $this->searchBy = 'qr_code';
        if ($this->search) {
            $guest = Guest::where('qr_code', $this->search)
                ->where('terminated_at', null)
                ->where('check_in_at', '!=', null)
                ->where('branch_id', auth()->user()->branch->id)
                ->first();
            if (!$guest) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'QR Code not found'
                );
                $this->guest = null;
                $this->search = '';
                $this->searchBy = null;
                return;
            }
            $this->guest = $guest;
            $this->queryString['search'] = $this->search;
        }
    }

    public function searchByRoomNumber()
    {
        $this->searchBy = 'room_number';
        if ($this->search) {
            $room = Room::where('number', $this->search)
                ->whereHas('floor', function ($query) {
                    $query->where('branch_id', auth()->user()->branch_id);
                })
                ->where('room_status_id', 2)
                ->first();
            if (!$room) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Room is currently not occupied.'
                );
                $this->guest = null;
                $this->search = '';
                $this->searchBy = null;
                return;
            }
            $check_in_detail = CheckInDetail::where('room_id', $room->id)
                ->where('check_in_at', '!=', null)
                ->where('check_out_at', null)
                ->first();
            if (!$check_in_detail) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Guest not found or already checked out.'
                );
                $this->guest = null;
                $this->search = '';
                $this->searchBy = null;
                return;
            }
            if ($check_in_detail->transaction->guest->terminated_at != null) {
                $this->guest = null;
                $this->search = '';
                $this->searchBy = null;
                return;
            }
            $this->guest = $check_in_detail->transaction->guest;
        }
    }

    public function searchByName()
    {
        $this->dialog()->info(
            $title = 'Reminder',
            $description = 'Guest may have the same name. Results may not be accurate. Please counter check the guest\'s qr coder / room number / contact number.'
        );
        $this->searchBy = 'name';
        if ($this->search) {
            $guest = Guest::where('name', $this->search)
                ->where('terminated_at', null)
                ->where('check_in_at', '!=', null)
                ->where('totaly_checked_out', false)
                ->where('branch_id', auth()->user()->branch->id)
                ->first();
            if (!$guest) {
                $this->notification()->error(
                    $title = 'Error!',
                    $message = 'Guest not found or already checked out.'
                );
                $this->guest = null;
                $this->search = '';
                $this->searchBy = null;
                return;
            }
            $this->guest = $guest;
        }
    }

    public function payTransaction($transaction_id)
    {
        $this->dialog()->confirm([
            'title'       => 'Are you Sure?',
            'description' => 'This will mark this transaction as paid',
            'icon'        => 'question',
            'accept'      => [
                'label'  => 'Yes, Pay',
                'method' => 'confirmPayTransaction',
                'params' => $transaction_id,
            ],
            'reject' => [
                'label'  => 'No, cancel',
            ],
        ]);
    }

    public function confirmPayTransaction($transaction_id)
    {
        $transaction = $this->guest->transactions()->find($transaction_id);
        $transaction->update([
            'paid_at' => now(),
        ]);
        $this->notification()->success(
            $title = 'Transaction Paid',
            $description = 'Transaction has been marked as paid'
        );
    }

    public function clear()
    {
        $this->guest = null;
        $this->search = '';
        $this->searchBy = null;
    }

    public function mount()
    {
        if ($this->search) {
            switch ($this->searchBy) {
                case 'qr_code':
                    $this->searchByQrCode();
                    break;
                case 'room_number':
                    $this->searchByRoomNumber();
                    break;
                case 'name':
                    $this->searchByName();
                    break;
                default:
                    $this->searchByQrCode();
                    break;
            }
        }
    }

    public function checkOutFalse()
    {
        $this->notification()->error(
            $title = 'Error!',
            $description = 'Please pay all transactions first.'
        );
    }

    public function checkOut()
    {
        $has_unpaid_transaction = $this->guest->transactions()->where('paid_at', null)->exists();
        if ($has_unpaid_transaction) {
            $this->checkOutFalse();
            return;
        }
        $this->final_reminder = true;
        $this->dialog()->confirm([
            'title'       => 'Are you Sure?',
            'description' => 'This will check out this guest',
            'icon'        => 'question',
            'accept'      => [
                'label'  => 'Yes, Check Out',
                'method' => 'confirmCheckOut',
            ],
            'reject' => [
                'label'  => 'No, cancel',
            ],
        ]);
    }

    public function confirmCheckOut()
    {
        
        DB::beginTransaction();
        $this->guest->update([
            'totaly_checked_out' => true,
            'check_out_at' => now(),
        ]);
        $check_in_detail = $this->guest->transactions()->where('transaction_type_id', 1)->first()->check_in_detail;
        $check_in_detail->update([
            'check_out_at' => now(),
        ]);
        $check_in_detail->room->update([
            'room_status_id' => 7,
            'time_to_clean' => Carbon::now()->addHours(3),
        ]);
        DB::commit();
        $this->notification()->success(
            $title = 'Guest Checked Out',
            $description = 'Guest has been checked out'
        );
        $this->guest = null;
        $this->search = '';
        $this->searchBy = null;
    }
    public function render()
    {
        return view('livewire.front-desk.check-out-guest', [
            'transactions' => $this->guest ?
                $this->guest->transactions()
                ->with(['transaction_type', 'check_in_detail.room.type','room_change.toRoom.type',  'damage.hotel_item', 'guest_request_item.requestable_item'])
                ->orderBy('created_at', $this->transactionOrder)->get() : [],
        ]);
    }
}
