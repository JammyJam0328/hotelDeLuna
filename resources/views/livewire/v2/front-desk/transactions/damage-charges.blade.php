<div x-data="{ formOpen: false }"
    x-on:close-form.window="formOpen=false">
    <div class="px-4 pt-4 sm:px-6 lg:px-8"
        x-animate>
        <div class="flex items-center justify-center">
            <div class="sm:flex-auto">
                <h1 class="text-lg font-semibold text-gray-900">
                    Damage Charges
                </h1>
            </div>
            <div>
                <x-my.button-primary py="py-1"
                    x-on:click="formOpen = !formOpen"
                    label="Add Damages" />
            </div>
        </div>
        <div x-cloak
            x-show="formOpen"
            x-collapse>
            <div class="p-4 mt-5 bg-gray-100 rounded-lg">
                <div>
                    <div class="grid grid-cols-1 gap-4">
                        <x-my.input.select label="Item"
                            required
                            placeholder="Select Item"
                            wire:model="hotelItemId">
                            @foreach ($hotelItems as $hotelItem)
                                <option value="{{ $hotelItem->id }}">
                                    {{ $hotelItem->name }}
                                </option>
                            @endforeach
                        </x-my.input.select>
                        <x-my.input label="Additional Amount"
                            numberOnly
                            wire:model.debounce.500ms="hotelItemAdditionalAmount" />
                        <x-my.input label="Occured At"
                            wire:model.defer="occurredAt"
                            type="datetime-local" />
                    </div>
                    <div class="pt-2 mt-2 border-t">
                        <dl class="pt-6 space-y-6 text-sm font-medium text-gray-500 border-t border-gray-200">
                            <div class="flex justify-between">
                                <dt>
                                    Item Amount
                                </dt>
                                <dd class="text-gray-900">
                                    ₱ {{ $hotelItemPrice ?? '0' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>
                                    Additional Amount
                                </dt>
                                <dd class="text-gray-900">
                                    ₱ {{ $hotelItemAdditionalAmount ?? '0' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between pt-6 text-gray-900 border-t border-gray-200">
                                <dt class="text-base">Total Payable Amount</dt>
                                <dd class="text-base">
                                    ₱
                                    {{ $hotelItemAdditionalAmount ? $hotelItemPrice + $hotelItemAdditionalAmount : $hotelItemPrice }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center space-x-3">
                        <x-my.button-secondary label="Return" />
                        <x-my.button-success
                            x-on:click="$dispatch('confirm',{
                                title : 'Are you sure?',
                                message : 'Are you sure you want to proceed?',
                                confirmButtonText : 'Confirm',
                                cancelButtonText : 'Cancel',
                                confirmMethod : 'confirmSaveRecord',
                            })"
                            label="Save Record" />
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col mt-3">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle">
                    <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-primary-600">
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6 lg:pl-8">
                                        Details
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-sm font-semibold text-white">
                                        Amount
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-sm font-semibold text-white">
                                        Transaction Date
                                    </th>
                                    <th scope="col"
                                        class="px-3 py-3.5 text-left text-sm font-semibold text-white">
                                        Paid at
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="py-3.5 pl-4 pr-3 text-xs text-gray-900 sm:pl-6 lg:pl-8">
                                            {{ $transaction->remarks }}
                                        </td>
                                        <td class="px-3 py-3.5 text-xs text-gray-900">
                                            {{ $transaction->payable_amount }}
                                        </td>

                                        <td class="px-3 py-3.5 text-xs text-gray-900">
                                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-3 py-3.5 text-right text-xs font-medium">
                                            <div class="flex space-x-3">
                                                @if ($transaction->paid_at == null)
                                                    <x-my.button-success label="Pay"
                                                        x-on:click="$dispatch('confirm',{
                                                            title : 'Are you sure?',
                                                            message : 'Are you sure you want to proceed?',
                                                            confirmButtonText : 'Confirm',
                                                            cancelButtonText : 'Cancel',
                                                            confirmMethod : 'payTransaction',
                                                            confirmParams :{{ $transaction->id }},
                                                    })"
                                                        py="py-1" />
                                                    <x-my.button-warning label="Pay With Deposit"
                                                        wire:click="payWithDeposit({{ $transaction->id }}, {{ $transaction->payable_amount }})"
                                                        py="py-1" />
                                                @else
                                                    {{ Carbon\Carbon::parse($transaction->paid_at)->format('M d, Y h:i A') }}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="py-3.5 pl-4 pr-3 text-center text-xs text-gray-900 sm:pl-6 lg:pl-8">
                                            No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
