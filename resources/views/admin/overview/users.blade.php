@extends('layouts.skydash-h')
@section('title', 'Users')
@section('heading', 'System Users')

@push('styles')
<style>
    .directory-page { --dir-border: #e2e8f0; --dir-ink: #183153; --dir-muted: #708096; --dir-blue: #2563c5; padding: .5rem .75rem 1.5rem; }
    .directory-heading { align-items: flex-end; display: flex; gap: 1rem; justify-content: space-between; }
    .directory-kicker { align-items: center; color: var(--dir-blue); display: flex; font-size: .75rem; font-weight: 800; gap: .5rem; letter-spacing: .11em; text-transform: uppercase; }
    .directory-kicker::before { background: var(--dir-blue); border-radius: 2px; content: ''; height: 3px; width: 24px; }
    .directory-heading h2 { color: var(--dir-ink); font-size: 1.7rem; font-weight: 750; letter-spacing: -.02em; }
    .directory-heading p { color: var(--dir-muted); font-size: .86rem; line-height: 1.55; max-width: 680px; }
    .directory-readonly { align-items: center; background: #eff6ff; border: 1px solid #d7e7ff; border-radius: 9px; color: #315d96; display: inline-flex; font-size: .75rem; font-weight: 700; gap: .55rem; padding: .7rem .9rem; white-space: nowrap; }
    .directory-readonly i { font-size: 1rem; }

    .directory-stats { display: grid; gap: 1rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .directory-stat { align-items: center; background: #fff; border: 1px solid var(--dir-border); border-radius: 12px; display: flex; gap: .9rem; min-height: 88px; padding: 1rem 1.1rem; }
    .directory-stat-icon { align-items: center; background: var(--stat-bg); border-radius: 10px; color: var(--stat-color); display: flex; flex: 0 0 48px; font-size: 1.2rem; height: 48px; justify-content: center; }
    .directory-stat-icon i { line-height: 1; }
    .directory-stat strong { color: var(--dir-ink); display: block; font-size: 1.35rem; line-height: 1; }
    .directory-stat span { color: var(--dir-muted); display: block; font-size: .74rem; margin-top: .3rem; }
    .stat-blue { --stat-bg: #e9f1ff; --stat-color: #2864c7; }
    .stat-violet { --stat-bg: #f1edff; --stat-color: #7654c7; }
    .stat-green { --stat-bg: #e8f7f0; --stat-color: #248661; }

    .directory-shell { align-items: start; display: grid; gap: 1.1rem; grid-template-columns: 250px minmax(0, 1fr); }
    .directory-rail, .directory-main { background: #fff; border: 1px solid var(--dir-border); border-radius: 14px; }
    .directory-rail { overflow: hidden; position: sticky; top: 1rem; }
    .rail-header { border-bottom: 1px solid #edf1f5; padding: 1.15rem; }
    .rail-header strong { color: var(--dir-ink); display: block; font-size: .86rem; }
    .rail-header span { color: #91a0b3; display: block; font-size: .7rem; line-height: 1.4; margin-top: .25rem; }
    .role-filters { list-style: none; margin: 0; padding: .7rem; }
    .role-filter { align-items: center; background: transparent; border: 0; border-radius: 8px; color: #5e6f84; display: flex; font-size: .76rem; font-weight: 600; gap: .65rem; min-height: 42px; padding: .7rem .75rem; text-align: left; transition: .15s; width: 100%; }
    .role-filter:hover { background: #f6f8fb; color: #244b7e; }
    .role-filter.active { background: #eaf2ff; color: #245eb8; }
    .role-filter i { font-size: 1rem; text-align: center; width: 18px; }
    .role-filter-label { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .role-filter-count { background: rgba(112, 128, 150, .12); border-radius: 12px; font-size: .67rem; min-width: 25px; padding: .18rem .42rem; text-align: center; }
    .role-filter.active .role-filter-count { background: #fff; }

    .directory-main { min-width: 0; overflow: hidden; }
    .directory-tools { align-items: center; border-bottom: 1px solid #edf1f5; display: flex; gap: 1rem; justify-content: space-between; min-height: 76px; padding: 1rem 1.15rem; }
    .result-copy strong { color: var(--dir-ink); display: block; font-size: .86rem; }
    .result-copy span { color: #91a0b3; display: block; font-size: .7rem; margin-top: .2rem; }
    .directory-search { max-width: 360px; position: relative; width: 100%; }
    .directory-search i { color: #8192a8; font-size: .9rem; left: .85rem; position: absolute; top: 50%; transform: translateY(-50%); }
    .directory-search .form-control { background: #f8fafc; border: 1px solid #dce4ed; border-radius: 8px; font-size: .77rem; height: 42px; padding-left: 2.35rem; }
    .directory-search .form-control:focus { background: #fff; border-color: #7fa9ec; box-shadow: 0 0 0 3px rgba(37, 99, 197, .09); }
    .directory-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 1.15rem; }
    .person-card { background: #fff; border: 1px solid #e4eaf1; border-radius: 11px; min-width: 0; overflow: hidden; padding: 1.1rem; position: relative; transition: border-color .15s, box-shadow .15s, transform .15s; }
    .person-card::before { background: var(--role-color); bottom: 0; content: ''; left: 0; position: absolute; top: 0; width: 3px; }
    .person-card:hover { border-color: #c8d6e7; box-shadow: 0 7px 18px rgba(24, 49, 83, .07); transform: translateY(-1px); }
    .person-top { align-items: flex-start; display: flex; gap: .85rem; }
    .person-avatar { align-items: center; background: var(--avatar-bg); border: 1px solid var(--avatar-border); border-radius: 11px; color: var(--role-color); display: flex; flex: 0 0 48px; font-size: .78rem; font-weight: 800; height: 48px; justify-content: center; letter-spacing: .035em; }
    .person-primary { min-width: 0; }
    .person-name { color: #213753; display: block; font-size: .86rem; font-weight: 750; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .person-username { color: #8796aa; display: block; font-size: .72rem; margin-top: .18rem; }
    .person-role { background: var(--avatar-bg); border-radius: 5px; color: var(--role-color); display: inline-block; font-size: .65rem; font-weight: 750; margin-top: .5rem; max-width: 100%; overflow: hidden; padding: .28rem .45rem; text-overflow: ellipsis; white-space: nowrap; }
    .person-scope { align-items: center; border-top: 1px solid #eef2f6; color: #607188; display: flex; font-size: .73rem; gap: .5rem; margin-top: .9rem; padding-top: .8rem; }
    .person-scope i { color: var(--role-color); font-size: .95rem; text-align: center; width: 16px; }
    .person-scope span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .person-scope-wide { color: #8796aa; }
    .directory-empty { color: #8492a6; grid-column: 1 / -1; padding: 3.5rem 1rem; text-align: center; }
    .directory-empty-icon { align-items: center; background: #f1f5f9; border-radius: 50%; color: #9eacbc; display: flex; font-size: 1.4rem; height: 56px; justify-content: center; margin: 0 auto .7rem; width: 56px; }
    .directory-empty strong { color: #52647a; display: block; font-size: .8rem; margin-bottom: .2rem; }
    .directory-empty span { font-size: .68rem; }

    @media (max-width: 991px) { .directory-shell { grid-template-columns: 1fr; } .directory-rail { position: static; } .role-filters { display: flex; gap: .35rem; overflow-x: auto; padding: .65rem; } .role-filter { flex: 0 0 auto; width: auto; } .role-filter-label { max-width: 160px; } .rail-header { display: none; } }
    @media (max-width: 767px) { .directory-page { padding: .25rem 0 1rem; } .directory-heading { align-items: flex-start; flex-direction: column; } .directory-readonly { white-space: normal; } .directory-stats { grid-template-columns: 1fr; } .directory-tools { align-items: flex-start; flex-direction: column; } .directory-search { max-width: none; } .directory-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $totalUsers = $users->sum(fn ($group) => $group->count());
    $assignedUsers = $users->flatten()->filter(fn ($user) => $user->municipality_id || $user->gov_agency_id)->count();
    $rolePalette = [
        ['color' => '#2864c7', 'background' => '#eaf2ff', 'border' => '#d5e5ff'],
        ['color' => '#7654c7', 'background' => '#f2edff', 'border' => '#e4dbff'],
        ['color' => '#248661', 'background' => '#e9f7f1', 'border' => '#d2eee1'],
        ['color' => '#b66b16', 'background' => '#fff4e5', 'border' => '#f8e1c0'],
        ['color' => '#b64b61', 'background' => '#fff0f3', 'border' => '#f5d9df'],
        ['color' => '#397d8c', 'background' => '#eaf7f9', 'border' => '#d2ebef'],
    ];
@endphp

<div class="directory-page">
    <header class="directory-heading mb-4">
        <div>
            <div class="directory-kicker mb-2">Access directory</div>
            <h2 class="mb-2">People and assignments</h2>
            <p class="mb-0">Browse SH1ELD accounts by system role and confirm each user's organizational assignment.</p>
        </div>
        <div class="directory-readonly"><i class="fa fa-eye" aria-hidden="true"></i> Read-only directory · Managed by Super Admin</div>
    </header>

    <section class="directory-stats mb-4" aria-label="Directory summary">
        <div class="directory-stat"><span class="directory-stat-icon stat-blue"><i class="fa fa-users" aria-hidden="true"></i></span><div><strong>{{ number_format($totalUsers) }}</strong><span>Registered accounts</span></div></div>
        <div class="directory-stat"><span class="directory-stat-icon stat-violet"><i class="fa fa-shield" aria-hidden="true"></i></span><div><strong>{{ number_format($users->count()) }}</strong><span>Authorized roles represented</span></div></div>
        <div class="directory-stat"><span class="directory-stat-icon stat-green"><i class="fa fa-building" aria-hidden="true"></i></span><div><strong>{{ number_format($assignedUsers) }}</strong><span>Municipal or agency assignments</span></div></div>
    </section>

    <div class="directory-shell">
        <aside class="directory-rail" aria-label="Filter users by role">
            <div class="rail-header"><strong>System roles</strong><span>Select a role to narrow the directory</span></div>
            <ul class="role-filters">
                <li><button type="button" class="role-filter active" data-role-filter="all"><i class="fa fa-users" aria-hidden="true"></i><span class="role-filter-label">All users</span><span class="role-filter-count">{{ $totalUsers }}</span></button></li>
                @foreach ($users as $role => $group)
                    <li><button type="button" class="role-filter" data-role-filter="{{ $role }}"><i class="fa fa-shield" aria-hidden="true"></i><span class="role-filter-label">{{ config("shield.roles.$role.label", str($role)->replace('_', ' ')->title()) }}</span><span class="role-filter-count">{{ $group->count() }}</span></button></li>
                @endforeach
            </ul>
        </aside>

        <section class="directory-main" aria-labelledby="directory-results-title">
            <div class="directory-tools">
                <div class="result-copy"><strong id="directory-results-title">Account directory</strong><span><span id="directory-visible-count">{{ $totalUsers }}</span> users shown</span></div>
                <div class="directory-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <label for="directory-search" class="sr-only">Search the user directory</label>
                    <input id="directory-search" type="search" class="form-control" placeholder="Search people or assignments" autocomplete="off">
                </div>
            </div>

            <div class="directory-grid" id="directory-grid">
                @forelse ($users as $role => $group)
                    @php
                        $roleLabel = config("shield.roles.$role.label", str($role)->replace('_', ' ')->title());
                        $palette = $rolePalette[$loop->index % count($rolePalette)];
                    @endphp
                    @foreach ($group as $u)
                        @php
                            $assignment = $u->municipality?->name ?? $u->govAgency?->acronym;
                            $initials = collect(preg_split('/\s+/', trim($u->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
                            $searchText = str($u->name.' '.$u->username.' '.$roleLabel.' '.($assignment ?? 'system-wide'))->lower();
                        @endphp
                        <article class="person-card" data-person-card data-role="{{ $role }}" data-search="{{ $searchText }}" style="--role-color: {{ $palette['color'] }}; --avatar-bg: {{ $palette['background'] }}; --avatar-border: {{ $palette['border'] }};">
                            <div class="person-top">
                                <div class="person-avatar" aria-hidden="true">{{ $initials ?: 'U' }}</div>
                                <div class="person-primary">
                                    <span class="person-name" title="{{ $u->name }}">{{ $u->name }}</span>
                                    <span class="person-username">{{ '@'.$u->username }}</span>
                                    <span class="person-role">{{ $roleLabel }}</span>
                                </div>
                            </div>
                            <div class="person-scope {{ $assignment ? '' : 'person-scope-wide' }}">
                                <i class="fa {{ $u->municipality ? 'fa-map-marker' : ($u->govAgency ? 'fa-building-o' : 'fa-globe') }}" aria-hidden="true"></i>
                                <span title="{{ $assignment ?? 'System-wide access' }}">{{ $assignment ?? 'System-wide access' }}</span>
                            </div>
                        </article>
                    @endforeach
                @empty
                    <div class="directory-empty"><div class="directory-empty-icon"><i class="fa fa-user-times" aria-hidden="true"></i></div><strong>No registered users</strong><span>The directory does not contain any accounts.</span></div>
                @endforelse

                <div class="directory-empty d-none" id="directory-empty" role="status"><div class="directory-empty-icon"><i class="fa fa-search" aria-hidden="true"></i></div><strong>No matching accounts</strong><span>Try another search or select a different role.</span></div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var search = document.getElementById('directory-search');
    var count = document.getElementById('directory-visible-count');
    var empty = document.getElementById('directory-empty');
    var buttons = document.querySelectorAll('[data-role-filter]');
    var activeRole = 'all';

    if (!search || !count || !empty) return;

    function filterDirectory() {
        var query = search.value.trim().toLowerCase();
        var visible = 0;
        document.querySelectorAll('[data-person-card]').forEach(function (card) {
            var matchesRole = activeRole === 'all' || card.dataset.role === activeRole;
            var matchesSearch = !query || (card.dataset.search || '').indexOf(query) !== -1;
            var show = matchesRole && matchesSearch;
            card.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        count.textContent = visible;
        empty.classList.toggle('d-none', visible !== 0);
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeRole = button.dataset.roleFilter;
            buttons.forEach(function (item) { item.classList.toggle('active', item === button); });
            filterDirectory();
        });
    });
    search.addEventListener('input', filterDirectory);
})();
</script>
@endpush
