<div>
    <div>
        <x-table :headers="['Qr Code', 'Name', 'Contact Number', 'Check In At', '']">
            <x-slot:topLeft>
                <x-input icon="search"
                    wire:model.debounce="search"
                    placeholder="Search..." />
            </x-slot:topLeft>
            @forelse ($guests as $guest)
                <x-table-row>
                    <x-table-data>
                        {{ $guest->qr_code }}
                    </x-table-data>
                    <x-table-data>
                        {{ $guest->name }}
                    </x-table-data>
                    <x-table-data>
                        {{ $guest->contact_number }}
                    </x-table-data>
                    <x-table-data>
                        {{ $guest->check_in_at }}
                    </x-table-data>
                    <x-table-data>

                    </x-table-data>
                </x-table-row>
            @empty
                <x-table-empty rows="5" />
            @endforelse
            <x-slot:pagination>
                {{ $guests->links() }}
            </x-slot:pagination>
        </x-table>
    </div>
</div>
