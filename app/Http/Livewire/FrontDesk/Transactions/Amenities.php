<?php

namespace App\Http\Livewire\FrontDesk\Transactions;

use App\Models\Amenity;
use App\Models\RequestableItem;
use App\Models\Transaction;
use Livewire\Component;
use App\Traits\{WithCaching, PayTransaction};
use WireUi\Traits\Actions;
class Amenities extends Component
{
    use Actions, WithCaching, PayTransaction;

    public $guest_id;

    public $current_room_id;

    public $payWithDepositsModal=false;

    public $form;

    public $loaded = false;

    public $requestableItems = [];

    public $remarks;


    public function rules()
    {
        return [
            'form.guest_id' => 'required',
            'form.requestable_item_id' => 'required',
            'form.quantity' => 'required|numeric',
            'form.price' => 'required|numeric',
            'form.additional_charge' => 'nullable|numeric',
            'form.front_desk_names' => 'nullable',
        ];
    }

   

    public function save()
    {
        $this->validate();

        Transaction::create([
            'branch_id' => auth()->user()->branch_id,
            'guest_id' => $this->guest_id,
            'transaction_type_id' => 8,
            'room_id' => $this->current_room_id,
            'payable_amount' => $this->form->price + $this->form->additional_charge,
            'remarks' => $this->remarks,
            'assigned_frontdesks'=>auth()->user()->assigned_frontdesks,
        ]);

        $this->form->save(); //ssss

        $this->makeNewForm();

        $this->dialog()->success(
            $title = 'Success',
            $message = 'Successfully Saved!'
        );

        $this->emit('transactionUpdated');
    }

    public function makeNewForm()
    {
        $ids = json_decode(auth()->user()->assigned_frontdesks);
        $frontdesks = Frontdesk::whereIn('id',$ids)->get();
        $this->form = Amenity::make([
            'guest_id' => $this->guest_id,
            'quantity' => 1,
            'front_desk_names' => $frontdesks->pluck('name')->implode(' and '),
        ]);
    }

    public function getTransactionsQueryProperty()
    {
        return \App\Models\Transaction::where('transaction_type_id', 8)->where('guest_id', $this->guest_id);
    }

    public function getTransactionsProperty()
    {
        return $this->cache(function () {
            return $this->transactionsQuery->get();
        }, 'transactions');
    }

    public function updatedFormRequestableItemId()
    {
        $this->useCacheRows();
        $amenity = RequestableItem::find($this->form->requestable_item_id);
        $this->form->price = $amenity->price;
        $this->remarks = 'Amenity: ' . $amenity->name;
    }

    public function updatedFormQuantity()
    {
        $this->useCacheRows();
        $this->validateOnly('form.quantity');
        $this->form->price = $this->form->quantity * $this->form->price;
    }

    public function componentIsLoaded()
    {
        $this->loaded = true;
        $this->requestableItems = RequestableItem::where('branch_id', auth()->user()->branch_id)->get();
    }

    public function mount()
    {
        $this->makeNewForm();
    }
   
    public function render()
    {
        return view('livewire.front-desk.transactions.amenities',[
            'transactions' => $this->loaded ? $this->transactions : [],
            'deposits'=> $this->payWithDepositsModal ? $this->deposits : [],
        ]);
    }

    public function getDepositsQueryProperty()
    {
        return \App\Models\Deposit::where('guest_id', $this->guest_id);
    }

    public function getDepositsProperty()
    {
        return $this->cache(function () {
            return $this->depositsQuery->get();
        }, 'deposits');
    }

    public $transactionId, $transactionAmount;


    public function payWithDeposits($transactionId,$transactionAmount)
    {
        $this->transactionId = $transactionId;
        $this->transactionAmount = $transactionAmount;
        $this->payWithDepositsModal = true;
    }
}
