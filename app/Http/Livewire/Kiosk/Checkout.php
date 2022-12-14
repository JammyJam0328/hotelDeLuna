<?php

namespace App\Http\Livewire\Kiosk;

use App\Models\Guest;
use App\Models\Transaction;
use Livewire\Component;

class Checkout extends Component
{
    public $scanner;
    public $search;

    public $guest;

    public $scannerpanel = true;

    public function render()
    {
        return view('livewire.kiosk.checkout', [
            'transactions' => Transaction::where(
                'guest_id',
                'like',
                '%' . $this->guest . '%'
            )
                ->where('branch_id', auth()->user()->branch_id)
                ->get()
                ->groupBy('transaction_type_id'),
            'guests' => Guest::where(
                'qr_code',
                'like',
                '%' . $this->scanner . '%'
            )
                ->where('branch_id', auth()->user()->branch_id)
                ->first(),
        ]);
    }

    // public function updatedScanner()
    // {
    //     $this->guest = Guest::where(
    //         'qr_code',
    //         'like',
    //         '%' . $this->scanner . '%'
    //     )
    //         ->where('branch_id', auth()->user()->branch_id)
    //         ->first()->id;
    //     $this->scannerpanel = false;
    // }

    public function searchCode()
    {
        $query = Guest::where('qr_code', $this->scanner)->first();
        if ($query) {
            $this->guest = $query->id;
            $this->scannerpanel = false;
        } else {
            dd('not found');
        }
    }
}
