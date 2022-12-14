<div x-data
    class="grid space-y-4">
    {{-- bulk actions --}}
    <div class="sm:flex sm:items-center sm:justify-between">
        <div class="mt-1 flex space-x-2 sm:flex-none">
            <x-my.button-primary label="Add New"
                wire:click="create">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </x-slot>
            </x-my.button-primary>
        </div>
        <div class="flex">
            <x-my.input.search wire:model.debounce="search" />
        </div>

    </div>
    {{-- table --}}
    <x-my.table>
        <x-slot name="header">
            <x-my.table.head name="Staying Hour" />
            <x-my.table.head name="Amount" />
            <x-my.table.head name="Type" />
            <x-my.table.head name="Status" />
            <x-my.table.head name="" />
        </x-slot>
        @foreach ($types as $type)
            <tr>
                <x-my.table.cell colspan="4"
                    class="bg-gray-50 font-bold">
                    {{ $type->name }}
                </x-my.table.cell>
            </tr>
            @forelse ($type->rates as $rate)
                <tr wire:key="{{ $rate->id }}">
                    <x-my.table.cell>
                        {{ $rate->staying_hour->number }}
                    </x-my.table.cell>
                    <x-my.table.cell>
                        {{ $rate->amount }}
                    </x-my.table.cell>
                    <x-my.table.cell>
                        {{ $rate->type->name }}
                    </x-my.table.cell>
                    <x-my.table.cell>
                        <div>
                            @if ($rate->is_available)
                                <x-my.button-success py="py-1"
                                    x-on:click="$dispatch('confirm', {
                                        title : 'Are you sure?',
                                        message : 'This will make this rate unavailable.',
                                        confirmButtonText : 'Continue', 
                                        cancelButtonText : 'No', 
                                        confirmMethod : 'markAsUnavailable',
                                        'confirmParams' : {{ $rate->id }}
                                    } )"
                                    label="Available" />
                            @else
                                <x-my.button-danger py="py-1"
                                    x-on:click="$dispatch('confirm', {
                                    title : 'Are you sure?',
                                    message : 'This will make this rate available.',
                                    confirmButtonText : 'Continue', 
                                    cancelButtonText : 'No', 
                                    confirmMethod : 'markAsAvailable',
                                    'confirmParams' : {{ $rate->id }}
                                } )"
                                    label="Not Available" />
                            @endif
                        </div>
                    </x-my.table.cell>
                    <x-my.table.cell>
                        <div class="flex justify-end px-2">
                            <x-my.edit-button wire:click="edit({{ $rate->id }})" />
                        </div>
                    </x-my.table.cell>
                </tr>
            @empty
                <x-my.table.empty span="4" />
            @endforelse
        @endforeach
    </x-my.table>

    {{-- modals --}}

    <div>
        <form wire:submit.prevent="save">
            @csrf
            <x-my.modal title="{{ $editMode ? 'Edit Rate' : 'Create Rate' }}"
                :showOn="['show-create-modal', 'show-edit-modal']"
                :closeOn="['close-create-modal', 'close-edit-modal']">
                <x-my.alert.error />
                <div class="grid space-y-4">
                    <div>
                        <x-my.input.select required
                            placeholder="Select Number of Hours"
                            wire:model.defer="form.staying_hour_id"
                            label="Selec Number of Hours">
                            @foreach ($stayingHours as $stayingHour)
                                <option value="{{ $stayingHour->id }}">
                                    {{ $stayingHour->number }}
                                </option>
                            @endforeach
                        </x-my.input.select>
                    </div>
                    <div>
                        <x-my.input label="Amount"
                            numberOnly
                            wire:model.defer="form.amount"
                            required />
                    </div>
                    <div>
                        <x-my.input.select required
                            placeholder="Select Type"
                            wire:model.defer="form.type_id"
                            label="Selec Type">
                            @foreach ($roomTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </x-my.input.select>
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex space-x-3">
                        <x-my.button-secondary x-on:click="close"
                            label="Cancel" />
                        <x-my.button-success type="submit"
                            loadingOn="save"
                            label="Save" />
                    </div>
                </x-slot>
            </x-my.modal>
        </form>
    </div>
</div>
