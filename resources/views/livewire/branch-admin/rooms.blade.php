<div>
    <div>
        <x-table :headers="['Room Number', 'Floor', 'Status', 'Types', '']">
            <x-slot:topLeft>
                <x-input placeholder="Search"
                    wire:model.debounce.500ms="search"
                    icon="search" />
                <x-native-select wire:model.debounce="filter.floor">
                    <option value="all">Floor (All)</option>
                    @foreach ($floors as $key => $floor)
                        <option value="{{ $floor->id }}">{{ ordinal($floor->number) }}</option>
                    @endforeach
                </x-native-select>
                <x-native-select wire:model.debounce="filter.room_status">
                    <option value="all">Status (All)</option>
                    @foreach ($roomStatuses as $key => $roomStatuse)
                        <option value="{{ $roomStatuse->id }}">{{ $roomStatuse->name }}</option>
                    @endforeach
                </x-native-select>
            </x-slot:topLeft>
            <x-slot:topRight>
                <div class="flex items-center space-x-3">
                    <x-button wire:click="$set('manageFloorModal',true)"
                        label="Manage Floor" />
                    <x-button primary
                        wire:click="add"
                        label="Add Room" />
                </div>
            </x-slot:topRight>
            @forelse ($rooms as $room)
                <x-table-row>
                    <x-table-data>
                        ROOM # {{ $room->number }}
                    </x-table-data>
                    <x-table-data>
                        {{ ordinal($room->floor->number) }} Floor
                    </x-table-data>
                    <x-table-data>
                        {{ $room->room_status->name }}
                    </x-table-data>
                    <x-table-data>
                        {{ $room->type->name }}
                    </x-table-data>
                    <x-table-data>
                        <div class="flex justify-end px-2">
                            <x-actions.edit wire:key="{{ $room->id }}"
                                wire:click="edit({{ $room->id }})"
                                wire:loading.class="cursor-progress"
                                wire:loading.attr="disabled"
                                wire:target="edit({{ $room->id }})" />
                        </div>
                    </x-table-data>
                </x-table-row>
            @empty
                <x-table-empty rows="5" />
            @endforelse
            <x-slot:pagination>
                {{ $rooms->links() }}
            </x-slot:pagination>
        </x-table>
    </div>
    <div wire:key="modal-panel">
        <x-modal.card title="{{ $this->getModeTitle() }}"
            wire:model.defer="showModal">
            <form>
                @csrf
                <div class="gap-3 sm:grid sm:grid-cols-3">
                    <x-input label="Number"
                        wire:model.defer="number"
                        type="number"
                        placeholder="Number" />
                    <x-native-select label="Select Floor"
                        wire:model.defer="floor_id">
                        <option value=""
                            disabled>Select Floor</option>
                        @foreach ($floors as $key => $floor)
                            <option value="{{ $floor->id }}">{{ $floor->number }}</option>
                        @endforeach
                    </x-native-select>
                    <x-native-select label="Select Room Status"
                        wire:model.defer="room_status_id">
                        <option value=""
                            disabled>Select Room Status</option>
                        @foreach ($roomStatuses as $key => $roomStatus)
                            <option value="{{ $roomStatus->id }}">{{ $roomStatus->name }}</option>
                        @endforeach
                    </x-native-select>
                    <div class="sm:col-span-3">
                        <x-textarea wire:model.defer="description"
                            label="Description"
                            placeholder="Leave it blank if none">
                        </x-textarea>
                    </div>
                    <div class="sm:col-span-3">
                        <x-native-select label="Select Room Type"
                            wire:model.defer="type_id">
                            <option value=""
                                disabled>Select Room Type</option>
                            @foreach ($roomTypes as $key => $roomType)
                                <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                            @endforeach
                        </x-native-select>
                    </div>
                </div>
            </form>
            <x-slot:footer>
                <div wire:key="action-buttons">
                    @switch($mode)
                        @case('create')
                            <x-button wire:click="create"
                                spinner="create"
                                positive
                                label="Save" />
                        @break

                        @case('edit')
                            <x-button wire:click="update"
                                spinner="update"
                                info
                                label="Update" />
                        @break

                        @default
                    @endswitch
                </div>
            </x-slot:footer>
        </x-modal.card>
        <x-modal.card title="Manage Floor"
            wire:model.defer="manageFloorModal">
            <form>
                @csrf
                <div>
                    <x-input label="Number"
                        wire:model.defer="floor_number"
                        type="number"
                        placeholder="Number" />
                </div>
            </form>
            <div class="grid mt-5 space-y-2">
                @foreach ($floors as $floors)
                    <div class="flex justify-between p-2 duration-150 ease-in-out border rounded-lg hover:bg-gray-100">
                        <div>
                            {{ ordinal($floors->number) }} Floor
                        </div>
                        <div>

                        </div>
                    </div>
                @endforeach
            </div>
            <x-slot:footer>
                <x-button primary
                    wire:click="saveFloor"
                    spinner="saveFloor"
                    label="Save" />
            </x-slot:footer>
        </x-modal.card>
    </div>
</div>