@extends('layouts.skydash-v')
@section('title', 'Assistance Records')
@section('heading', 'JAPIC Certification')

@section('content')
<a href="{{ route('japic.certifications.show', $processing) }}" class="d-inline-block mb-3">&larr; Back to certification profile</a>
<section class="card"><div class="card-header"><h2 class="h5 mb-0">Assistance Records</h2></div><div class="card-body"><p class="text-muted mb-0">No assistance records are currently available.</p></div></section>
@endsection
