@extends('layouts.skydash-v')
@section('title', 'FR Profiles')
@section('heading', 'FR Profiles')

@push('styles')
<style>
    .fr-list-page{--navy:#172b4d;--border:#e7ecf3}.fr-list-hero{align-items:center;background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:16px;box-shadow:0 10px 28px rgba(47,111,237,.16);color:#fff;display:flex;justify-content:space-between;overflow:hidden;padding:1.5rem;position:relative}.fr-list-hero::after{background:rgba(255,255,255,.08);border-radius:50%;content:'';height:190px;position:absolute;right:-45px;top:-95px;width:190px}.hero-content,.record-button{position:relative;z-index:1}.hero-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;opacity:.75;text-transform:uppercase}.fr-list-hero h2{color:#fff;font-size:1.55rem;font-weight:700}.fr-list-hero p{font-size:.8rem;opacity:.86}.record-button{align-items:center;background:#fff;border:1px solid rgba(255,255,255,.8);border-radius:11px;color:#204f9e;display:inline-flex;font-size:.76rem;font-weight:700;padding:.65rem .9rem;text-decoration:none!important}.record-button:hover{background:#f8fbff;color:#173f85}.list-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045);overflow:hidden}.filter-bar{align-items:end;background:#fff;border-bottom:1px solid #edf1f6;display:flex;flex-wrap:wrap;gap:.7rem;padding:1rem 1.2rem}.filter-field{min-width:155px}.filter-search{flex:1 1 240px}.filter-field label{color:#718096;display:block;font-size:.66rem;font-weight:700;margin-bottom:.25rem;text-transform:uppercase}.filter-actions{display:flex;gap:.45rem}.form-control{border-color:#dfe5ee;border-radius:8px}.records-table{margin:0}.records-table thead th{background:#f7f9fc;border:0;color:#718096;font-size:.66rem;font-weight:700;letter-spacing:.045em;padding:.85rem .9rem;text-transform:uppercase;white-space:nowrap}.records-table tbody td{border-color:#edf1f6;color:#52616f;font-size:.78rem;padding:.9rem;vertical-align:middle}.reference{color:#244a83;font-weight:700}.record-name{color:#334155;font-weight:700}.area-text{min-width:190px}.badge-readonly{border-radius:14px;display:inline-flex;font-size:.63rem;font-weight:700;padding:.3rem .6rem}.badge-yes{background:#fff5df;color:#9a6700}.badge-no{background:#f1f5f9;color:#64748b}.badge-new{background:#eaf1ff;color:#2f6fed}.unavailable-action{cursor:not-allowed;font-size:.7rem;opacity:.7;white-space:nowrap}.list-footer{align-items:center;border-top:1px solid #edf1f6;display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;padding:1rem 1.2rem}.pagination-summary{color:#7b8a9e;font-size:.72rem}.empty-state{color:#8492a6;padding:3.5rem 1rem;text-align:center}.empty-state i{color:#b7c4d5;display:block;font-size:2.4rem;margin-bottom:.55rem}.empty-state strong{color:#334155;display:block;margin-bottom:.25rem}@media(max-width:767px){.fr-list-hero{align-items:flex-start;flex-direction:column;gap:1rem;padding:1.1rem}.filter-field,.filter-search{flex:1 1 100%;min-width:100%}.filter-actions{width:100%}.filter-actions .btn{flex:1}.list-footer{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="fr-list-page">
    <header class="fr-list-hero mb-4">
        <div class="hero-content">
            <div class="hero-eyebrow">39th Infantry Battalion</div>
            <h2 class="mb-1">Surfaced FR Profiles</h2>
            <p class="mb-0">Authorized register of standalone surfaced former rebel records.</p>
        </div>
        <a href="{{ route('ib39.fr-profiles.create') }}" class="record-button"><i class="fa fa-user-plus mr-2" aria-hidden="true"></i>Record Surfaced FR</a>
    </header>

    <section class="card list-card" aria-label="Surfaced FR profiles">
        <form method="GET" action="{{ route('ib39.fr-profiles.index') }}" class="filter-bar">
            <div class="filter-field filter-search">
                <label for="search">Reference or name</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="form-control" placeholder="Search reference, first name, or last name" autocomplete="off">
            </div>
            <div class="filter-field">
                <label for="category">FR category</label>
                <select id="category" name="category" class="form-control">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected(($filters['category'] ?? null) === $category->value)>{{ $category->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="municipality_id">Municipality</label>
                <select id="municipality_id" name="municipality_id" class="form-control">
                    <option value="">All municipalities</option>
                    @foreach ($municipalities as $municipality)
                        <option value="{{ $municipality->id }}" @selected((string) ($filters['municipality_id'] ?? '') === (string) $municipality->id)>{{ $municipality->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="possessed_firearms">Firearms</label>
                <select id="possessed_firearms" name="possessed_firearms" class="form-control">
                    <option value="">All records</option>
                    <option value="1" @selected(($filters['possessed_firearms'] ?? null) === true)>Yes</option>
                    <option value="0" @selected(array_key_exists('possessed_firearms', $filters) && $filters['possessed_firearms'] === false)>No</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('ib39.fr-profiles.index') }}" class="btn btn-light">Reset Filters</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table records-table">
                <caption class="sr-only">Authorized surfaced former rebel profiles</caption>
                <thead><tr><th>FR Reference Number</th><th>Name</th><th>FR Category</th><th>Area of Surfacing</th><th>Date of Surfacing</th><th>Firearms Indicator</th><th>Overall Case Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td><span class="reference">{{ $record->reference_number }}</span></td>
                        <td><span class="record-name">{{ $record->display_name }}</span></td>
                        <td>{{ $record->category->value }}</td>
                        <td class="area-text">{{ collect([$record->barangay?->name, $record->municipality->name, $record->province])->filter()->join(', ') }}</td>
                        <td>{{ $record->surfaced_at->format('M d, Y') }}</td>
                        <td><span class="badge-readonly {{ $record->possessed_firearms ? 'badge-yes' : 'badge-no' }}">{{ $record->possessed_firearms ? 'Yes' : 'No' }}</span></td>
                        <td><span class="badge-readonly badge-new">{{ $record->overall_case_status }}</span></td>
                        <td><a href="{{ route('ib39.fr-profiles.show', $record) }}" class="btn btn-sm btn-outline-primary">View Profile</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="mdi mdi-account-search" aria-hidden="true"></i><strong>{{ $hasActiveFilters ? 'No matching FR profiles found' : 'No surfaced FR profiles recorded' }}</strong><span>{{ $hasActiveFilters ? 'Adjust or reset the filters to view other records.' : 'Use Record Surfaced FR to create the first authorized record.' }}</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->isNotEmpty())
            <footer class="list-footer">
                <div class="pagination-summary">Showing {{ number_format($records->firstItem()) }}–{{ number_format($records->lastItem()) }} of {{ number_format($records->total()) }} records</div>
                @if ($records->hasPages())<div>{{ $records->onEachSide(1)->links() }}</div>@endif
            </footer>
        @endif
    </section>
</div>
@endsection
