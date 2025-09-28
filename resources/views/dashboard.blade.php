@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Dashboard</h1>
        </div>

        <div class="section-body">
            <h2 class="section-title">Hi, {{ auth()->user()->name }}!</h2>
            <p class="section-lead">
                Selamat datang di Sistem Informasi Dokumen dan Surat Menyurat (SIDOCMA).
            </p>
    </section>
@endsection
