@extends('layouts.master')
@section('content')
    <x-layout.branch>
        <x-page-layout title="Check In">
            @livewire('front-desk.check-in')
        </x-page-layout>
    </x-layout.branch>
@endsection
