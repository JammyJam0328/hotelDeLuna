<?php

namespace App\Http\Livewire\FrontDesk\Transactions;

use App\Models\Deposit;
use Livewire\Component;
use App\Traits\{WithCaching, PayTransaction};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use WireUi\Traits\Actions;

class GuestDeposits extends Component
{
    use WithCaching, Actions, PayTransaction;

    public $guest_id;

    public $current_room_id;

    public $form;

    public $deductionAmount;

    public $deductionModal;

    public $selectedDeposit;

    public $loaded = false;
    public function rules()
    {
        return [
            'form.guest_id' => 'required|numeric',
            'form.amount' => 'required|numeric',
            'form.remarks' => 'nullable|string',
            'form.deducted' => 'nullable',
            'form.claimed_at' => 'nullable',
            'form.front_desk_names' => 'nullable',
        ];
    }

    public function componentIsLoaded()
    {
        $this->loaded = true;
    }

    public function makeNewForm()
    {
        $ids = json_decode(auth()->user()->assigned_frontdesks);
        $frontdesks = Frontdesk::whereIn('id',$ids)->get();
        $this->form = Deposit::make([
            'guest_id' => $this->guest_id,
            'front_desk_names' => $frontdesks->pluck('name')->implode(' and '),
        ]);
    }

    public function save()
    {
        $this->validate();
        DB::beginTransaction();

        \App\Models\Transaction::create([
            'branch_id' => auth()->user()->branch_id,
            'guest_id' => $this->guest_id,
            'transaction_type_id' => 2,
            'room_id' => $this->current_room_id,
            'payable_amount' => $this->form->amount,
            'paid_at' => Carbon::now(),
            'remarks' => $this->form->remarks,
            'assigned_frontdesks'=>auth()->user()->assigned_frontdesks
        ]);

        $this->form->save();

        DB::commit();

        $this->makeNewForm();

        $this->emit('transactionUpdated');

        $this->dialog()->success(
            $title = 'Success',
            $message = 'Deposit has been saved successfully',
        );
    }

    public function mount()
    {
        $this->makeNewForm();
    }

    public function getDepositsQuery()
    {
        return Deposit::query()
            ->where('guest_id', $this->guest_id)
            ->orderBy('created_at', 'DESC');
    }

    public function getDepositsProperty()
    {
        return $this->cache(function () {
            return $this->getDepositsQuery()->get();
        });
    }

    public function showDeductionModal(Deposit $deposit)
    {
        $this->useCacheRows();
        $this->selectedDeposit = $deposit;
        $this->deductionAmount = '';
        $this->deductionModal = true;
    }

    public function saveDeduction()
    {
        if ($this->selectedDeposit->amount == $this->selectedDeposit->deducted) {
            $this->dialog()->error(
                $title = 'Error',
                $message = 'Deposit is already fully deducted',
            );
            return;
        }

        if ($this->deductionAmount + $this->selectedDeposit->deducted > $this->selectedDeposit->amount) {
            $this->dialog()->error(
                $title = 'Error',
                $message = 'Deduction exceeds deposit amount',
            );
            return;
        }

        $this->validate([
            'deductionAmount' => 'required|numeric|max:' . $this->selectedDeposit->amount,
        ]);

        $this->selectedDeposit->update([
            'deducted' => $this->selectedDeposit->deducted + $this->deductionAmount,
            'claimed_at'=>  $this->deductionAmount + $this->selectedDeposit->deducted == $this->selectedDeposit->amount ? Carbon::now() : null,
        ]);

        $this->emit('transactionUpdated');

        $this->deductionModal= false;

        $this->deductionAmount='';

        $this->dialog()->success(
            $title = 'Success',
            $message = 'Deduction has been saved successfully',
        );
    }

    public function claimeDeposit(Deposit $deposit)
    {
        $this->useCacheRows();
        $this->form = $deposit;

        if ($this->form->claimed_at) {
            $this->dialog()->error(
                $title = 'Error',
                $message = 'Deposit is already claimed',
            );
            return;
        }
        
        if ($this->form->amount == $this->form->deducted) {
            $this->dialog()->error(
                $title = 'Error',
                $message = 'Deposit is already fully deducted',
            );
            return;
        }

        $this->dialog()->confirm([
            'title'       => 'Claimable Deposit',
            'description' => 'PHP ' . number_format($this->form->amount - $this->form->deducted, 2),
            'icon'        => 'question',
            'accept'      => [
                'label'  => 'Yes, Return Deposit',
                'method' => 'confirmClaimDeposit',
            ],
            'reject' => [
                'label'  => 'No, cancel',
            ],
        ]);

    }

    public function confirmClaimDeposit()
    {
        $this->form->update([
            'claimed_at' => Carbon::now(),
        ]);

        $this->emit('transactionUpdated');

        $this->dialog()->success(
            $title = 'Success',
            $message = 'Deposit has been claimed successfully',
        );
    }

    public function render()
    {
        return view('livewire.front-desk.transactions.guest-deposits',[
            'deposits' => $this->loaded ? $this->deposits : [],
        ]);
    }
}
