@extends('layouts.app')

@section('content')
    @livewire('user-form', ['userId' => $userId])
@endsection