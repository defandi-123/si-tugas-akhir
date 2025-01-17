@extends('layout.app')
@section('title', 'Halaman Dashboard Panitia')

@section('main')
    {{--  isi props dfengan data user yang dilempar ke halaman ini  --}}
    <x-common.navbar :nama="$data['nama']" :role="$data['role']"/>
    <x-sidebar.sidebarpanitia/>

    {{--    isi content main--}}
    <div class="p-4 sm:ml-64">
        <div class="rounded-lg dark:border-gray-700 mt-14">
            <x-cardview.cardviewpanitia/>
        </div>
    </div>

    {{-- Alert jika login sukses setelah itu login tidak akan muncul lagi menggunakan flash untuk session sementara setelah request otomatis dihapus --}}
    @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif
@endsection
