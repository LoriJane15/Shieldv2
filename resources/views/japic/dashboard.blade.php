@extends('layouts.skydash-v')
@section('title', 'JAPIC Dashboard')
@section('heading', 'JAPIC Certification')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h2 class="h4 font-weight-bold mb-1">Certification Dashboard</h2>
        <p class="text-muted mb-0">Read-only overview of surfaced FR certification intake.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('japic.certifications.index') }}">FRs for Certification</a>
</div>

@php
    $cards = [
        ['Total Tasks', (int) ($stats->total ?? 0), 'icon-layers'],
        ['Due Soon', (int) ($stats->due_soon ?? 0), 'icon-clock'],
        ['Overdue', (int) ($stats->overdue ?? 0), 'icon-exclamation'],
        ['Completed', (int) ($stats->completed ?? 0), 'icon-check'],
        ['Cancelled', (int) ($stats->cancelled ?? 0), 'icon-close'],
    ];
@endphp
<div class="row">
    @foreach($cards as [$label, $count, $icon])
        <div class="col-md-4 col-xl mb-3">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between"><span class="text-muted small">{{ $label }}</span><i class="{{ $icon }} text-primary"></i></div>
                <div class="h3 mt-3 mb-0">{{ number_format($count) }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row mt-2">
    <div class="col-lg-7 mb-4">
        <section class="card h-100" aria-labelledby="status-heading">
            <div class="card-body">
                <h3 id="status-heading" class="h6 font-weight-bold mb-3">Current status distribution</h3>
                @if($statusCounts->isEmpty())
                    <p class="text-muted mb-0">No certification tasks are currently available.</p>
                @else
                    @foreach($statusCounts as $status => $count)
                        <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $status }}</span><strong>{{ $count }}</strong></div>
                    @endforeach
                @endif
            </div>
        </section>
    </div>
    <div class="col-lg-5 mb-4">
        <section class="card h-100" aria-labelledby="notifications-heading">
            <div class="card-body">
                <h3 id="notifications-heading" class="h6 font-weight-bold mb-3">Recent notifications</h3>
                @forelse($notifications as $notification)
                    <a class="d-block border-bottom py-2 text-decoration-none" href="{{ route('japic.certifications.show', $notification->data['processing_id']) }}">
                        <strong>{{ $notification->data['fr_reference'] ?? 'Certification intake' }}</strong>
                        <span class="d-block small text-muted">Received {{ $notification->data['received_date'] ?? '—' }} · Due {{ $notification->data['due_date'] ?? '—' }}</span>
                    </a>
                @empty
                    <p class="text-muted mb-0">No intake notifications are available.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
