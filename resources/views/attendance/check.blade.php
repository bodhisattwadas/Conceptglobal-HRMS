@extends('layouts.openhrms', ['title' => 'Attendances'])
@include('attendance._nav')

@section('content')
    <div class="attendance-kiosk-bg">
        <section class="check-card">
            <div class="check-cover"></div>
            <div class="check-avatar" style="background: {{ $employee->card_color }}">
                @if ($employee->profile_photo_url)
                    <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                @else
                    {{ $employee->initials }}
                @endif
            </div>
            <div class="check-body">
                <h1>{{ $employee->full_name }}</h1>
                <h2>Want to {{ $isCheckedIn ? 'check out' : 'check in' }}?</h2>
                <div class="work-hours">Today's work hours: {{ $worked }}</div>
                <form method="post" action="{{ route('attendance.toggle') }}">
                    @csrf
                    <button class="check-button {{ $isCheckedIn ? 'checkout' : '' }}" title="Click to {{ $isCheckedIn ? 'check out' : 'check in' }}">
                        <i class="bi {{ $isCheckedIn ? 'bi-box-arrow-right' : 'bi-box-arrow-in-right' }}"></i>
                    </button>
                </form>
                <div class="click-label">Click to {{ $isCheckedIn ? 'check out' : 'check in' }}</div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .attendance-kiosk-bg {
            align-items: center;
            background: #aaa;
            display: flex;
            justify-content: center;
            min-height: calc(100vh - 40px);
            padding: 4rem 1rem;
        }
        .check-card {
            background: #fff;
            border-radius: 2px;
            overflow: hidden;
            position: relative;
            text-align: center;
            width: 550px;
        }
        .check-cover {
            background: linear-gradient(135deg, #8e819c, #dba4ad);
            height: 90px;
        }
        .check-avatar {
            align-items: center;
            border: 3px solid #fff;
            border-radius: 50%;
            color: #fff;
            display: flex;
            font-size: 1.4rem;
            font-weight: 700;
            height: 82px;
            justify-content: center;
            left: calc(50% - 41px);
            overflow: hidden;
            position: absolute;
            top: 38px;
            width: 82px;
        }
        .check-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .check-body {
            padding: 38px 30px 30px;
        }
        .check-body h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .check-body h2 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 24px;
        }
        .work-hours,
        .click-label {
            color: #6b7280;
            font-size: 16px;
            font-weight: 700;
            margin: 10px 0;
        }
        .check-button {
            align-items: center;
            background: #ffad0a;
            border: 0;
            border-radius: 8px;
            color: #344256;
            display: inline-flex;
            font-size: 88px;
            height: 140px;
            justify-content: center;
            width: 166px;
        }
        .check-button.checkout {
            background: #ffad0a;
        }
    </style>
@endpush
