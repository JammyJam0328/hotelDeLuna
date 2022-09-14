<div>
    <div>
        <x-modal.card title="CHECK IN"
            max-width="4xl"
            wire:model.defer="showModal">
            <div>
                @if ($guest)
                    <div>
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-2">
                                <dt class="text-sm font-medium text-gray-500">QR Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                    {{ $guest->qr_code }}
                                </dd>
                            </div>
                            <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-2">
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                    {{ $guest->name }}
                                </dd>
                            </div>
                            <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Contact Number
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                    {{ $guest->contact_number }}
                                </dd>
                            </div>
                            <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-2">
                                <dt class="text-sm font-bold text-gray-900">Transaction | Bills</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-3 sm:mt-0">
                                    <ul role="list"
                                        class="border border-gray-200 divide-y divide-gray-200 rounded-md">
                                        @foreach ($transactions as $transaction)
                                            <li class="flex items-center justify-between py-3 pl-3 pr-4 text-sm">
                                                <div class="flex items-center flex-1 w-0">
                                                    <span class="flex-1 w-0 ml-2 truncate">
                                                        {{ $transaction->transaction_type->name }} |
                                                        ₱{{ $transaction->payable_amount }} @if ($transaction->transaction_type_id == 1)
                                                            | ROOM # {{ $transaction->check_in_detail->room->number }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="flex-shrink-0 ml-4">

                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </dd>
                            </div>
                        </dl>
                        <div class="mt-5">
                            <h1 class="font-semibold text-gray-800">
                                Total Amount: ₱{{ $guest->transactions->sum('payable_amount') }}
                            </h1>
                        </div>
                    </div>
                @endif
            </div>
            <x-slot:footer>
                <x-button wire:click="confirmCheckIn"
                    positive
                    spinner="confirmCheckIn">
                    Pay and Check In
                </x-button>
            </x-slot:footer>
        </x-modal.card>
    </div>
</div>
