@extends('layouts.master')

@section('content')
    <x-layout.housekeeping>
        <div>
            <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
                <h1 class="text-2xl font-semibold text-gray-900">Designation</h1>
            </div>
            <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
                <div class="my-5">
                    @livewire('housekeeping.designations')
                </div>
            </div>
        </div>
    </x-layout.housekeeping>
@endsection
