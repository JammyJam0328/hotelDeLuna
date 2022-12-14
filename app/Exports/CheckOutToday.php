<?php

namespace App\Exports;

use App\Models\Guest;
use Carbon\Carbon;
use DateTime;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CheckOutToday implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Guest::where('branch_id', auth()->user()->branch_id)
            ->where('totaly_checked_out', 1)
            ->where('check_out_at', '>=', Carbon::today())
            ->with([
               'transactions'=>[
                    'check_in_detail'=>[
                        'room'=>[
                            'floor',
                            'type',
                        ],
                        'rate'=>[
                            'staying_hour'
                        ]
                    ]
               ]
            ])
            ->get();
    }

    public function map($guest): array
    {
        $check_in_at =  Carbon::parse($guest->check_in_at);
        $last_updated_at = $guest->updated_at;
        return [
            $check_in_at->format('M, d y h:i A'),
            $last_updated_at->format('M, d y h:i A'),
            $guest->name,
            $guest->contact_number,
            $guest->transactions->where('transaction_type_id', 1)->first()->check_in_detail->room->number,
        ];
    }

    public function headings(): array
    {
        return [
            'Time Check In',
            'Time Check Out',
            'Name',
            'Phone',
            'Room',
        ];
    }
}
