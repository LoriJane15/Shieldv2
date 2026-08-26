@props(['enrollment'])
@php($month = $enrollment->monitoringMonth())
<div class="monitoring-progress" aria-label="{{ $month ? "Month {$month} of 3" : 'Monitoring period unavailable' }}">
    <strong>{{ $month ? "Month {$month} of 3" : 'Not available' }}</strong>
    @if($month)
        <div class="monitoring-track" aria-hidden="true">
            @foreach(range(1, 3) as $step)<span class="monitoring-step @if($step <= $month) is-current @endif"></span>@endforeach
        </div>
    @endif
    @if($enrollment->needsAttention())<small>Completion verification is due</small>@endif
</div>
