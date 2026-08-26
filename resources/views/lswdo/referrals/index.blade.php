@extends('layouts.skydash-v')
@section('title', 'New Referrals')
@section('heading', 'New Referrals')

@section('content')
<div class="lswdo-referrals-page">
    <x-shield.module-header
        eyebrow="Case intake"
        title="Assigned MBLRC Referrals"
        description="Review referrals explicitly assigned to your account and initialize authorized E-CLIP cases."
        icon="mdi-inbox-arrow-down"
        role="LSWDO"
    />
<div class="card"><div class="card-body">
    <div class="shield-card-heading"><span class="shield-card-heading-icon"><i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i></span><div><h4>Referral Queue</h4><p>Only referrals explicitly assigned to your account appear here.</p></div></div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Referral</th><th>Beneficiary ID</th><th>Integration completed</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
            @forelse ($referrals as $referral)
                <tr>
                    <td class="fw-semibold">{{ $referral->referral_number }}</td>
                    <td>{{ $referral->formerRebel->classified_id }}</td>
                    <td>{{ $referral->enrollment->integration_completed_at?->format('M j, Y') ?? '—' }}</td>
                    <td><span class="badge {{ $referral->status === 'accepted' ? 'bg-success' : 'bg-warning text-dark' }}">{{ str($referral->status)->title() }}</span></td>
                    <td class="text-end">
                        @if ($referral->status === 'pending')
                            <form method="POST" action="{{ route('lswdo.referrals.accept', $referral) }}">
                                @csrf
                                <button class="btn btn-sm btn-primary">Accept and initialize case</button>
                            </form>
                        @elseif ($referral->eclipCase)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('lswdo.eclip.show', $referral->eclipCase) }}">Open case</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">No referrals are assigned to you.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $referrals->links() }}
</div></div>
</div>
@endsection
