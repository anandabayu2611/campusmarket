@extends('layouts.app')
@section('title', 'Profil Saya — CampusMarket')
@section('content')
<div class="card">
    @include('profile._card')
    <a class="btn" href="{{ route('profile.edit') }}">Edit profil</a>
</div>
@endsection
