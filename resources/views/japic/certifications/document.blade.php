<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="robots" content="noindex,nofollow,noarchive"><title>JAPIC Certification Draft</title>
<style>
    @page{size:A4 portrait;margin:0}*{box-sizing:border-box}body{margin:0;background:#e9ecef;color:#111;font-family:Arial,Helvetica,sans-serif}.toolbar{width:210mm;margin:12px auto;text-align:right}.toolbar a,.toolbar button{padding:8px 12px;margin-left:6px}.sheet{position:relative;width:210mm;min-height:297mm;margin:0 auto 16px;background:#fff;padding:15mm 19mm 14mm;font-size:10.5pt;line-height:1.42}.draft-mark{position:absolute;top:47%;left:18%;transform:rotate(-31deg);font-size:36pt;font-weight:700;color:rgba(150,0,0,.11);letter-spacing:3px;white-space:nowrap}.committee{display:grid;grid-template-columns:15mm auto 15mm;justify-content:center;align-items:center;gap:4mm;text-align:center;font-weight:700;font-size:12pt}.emblem-placeholder{width:14mm;height:14mm;border:1px dashed #777;border-radius:50%;font-size:5.5pt;color:#666;display:flex;align-items:center;justify-content:center;text-align:center}.headquarters{display:grid;grid-template-columns:1fr 1fr;gap:19mm;text-align:center;font-size:9.2pt;margin-top:4mm}.title{text-align:center;font-weight:700;text-decoration:underline;letter-spacing:7px;font-size:15pt;margin:7mm 0 4mm}.document-intro{min-height:43mm}.meta{line-height:1.65;padding-top:3mm}.photo{float:right;width:35mm;height:42mm;margin:0 0 4mm 7mm;border:1px solid #111;display:flex;align-items:center;justify-content:center;text-align:center;font-size:8pt;overflow:hidden;background:#fafafa}.photo img{width:100%;height:100%;object-fit:cover}.narrative{text-align:justify;text-indent:13mm;margin:5mm 0 0;line-height:1.62}.purpose{text-align:justify;text-indent:13mm;margin:5mm 8mm;line-height:1.62}.label{font-weight:700;margin-top:6mm}.personnel{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:18mm;row-gap:5mm;text-align:center}.person{break-inside:avoid;padding-top:9mm}.person .name{font-weight:700;border-bottom:1px solid #111;padding-bottom:1mm;text-transform:uppercase;min-height:6mm}.person .rank{font-size:9.5pt;margin-top:1mm}.copies{font-size:8.5pt;margin-top:7mm;break-inside:avoid}.copies strong{display:block}@media print{body{background:#fff}.toolbar{display:none}.sheet{margin:0;box-shadow:none}}@media(max-width:800px){.toolbar,.sheet{width:100%}.sheet{padding:10mm}.personnel{grid-template-columns:1fr}}
</style>
</head>
<body>
@php
    $certificate = $payload['certificate'];
    $narrative = $certificate['narrative_values'];
    $dateIssued = filled($certificate['date_issued'] ?? null) ? \Carbon\Carbon::parse($certificate['date_issued'])->format('d F Y') : '________________';
    $surrenderedOn = filled($narrative['surrendered_on'] ?? null) ? \Carbon\Carbon::parse($narrative['surrendered_on'])->format('d F Y') : '________________';
@endphp
@unless($printMode)<div class="toolbar"><a href="{{ route('japic.certifications.show', $processing) }}">Back</a><a href="{{ route('japic.certifications.print', $processing) }}">Print view</a><button type="button" onclick="window.print()">Print</button></div>@endunless
<main class="sheet">
    <div class="draft-mark">DRAFT — NOT FINAL</div>
    <header>
        <div class="committee"><div class="emblem-placeholder" aria-label="Left official emblem placeholder">Emblem<br>pending</div><div>JOINT AFP-PNP<br>INTELLIGENCE COMMITTEE</div><div class="emblem-placeholder" aria-label="Right official emblem placeholder">Emblem<br>pending</div></div>
        <div class="headquarters"><div><strong>GENERAL HEADQUARTERS<br>ARMED FORCES OF THE PHILIPPINES</strong><br>Camp General Emilio Aguinaldo<br>Quezon City</div><div><strong>NATIONAL HEADQUARTERS<br>PHILIPPINE NATIONAL POLICE</strong><br>Camp Brigadier General Rafael Crame<br>Quezon City</div></div>
        <div class="title">CERTIFICATION</div>
    </header>
    <div class="document-intro"><div class="photo">@if($photoDataUri)<img src="{{ $photoDataUri }}" alt="Selected certification photograph">@else Photograph unavailable @endif</div><div class="meta"><strong>Date Issued:</strong> {{ $dateIssued }}<br><strong>Control Number:</strong> {{ $certificate['control_number'] ?: '________________' }}</div></div>
    <p class="narrative"><strong>THIS IS TO CERTIFY THAT</strong> {{ $narrative['fr_name'] ?: '________________' }}, residing in {{ $narrative['residence'] ?: '________________' }}, is a former {{ $narrative['former_organization_or_category'] ?: '________________' }}, operating in the area/s of {{ $narrative['areas_of_operation'] ?: '________________' }}. She started her affiliation with the {{ $narrative['affiliated_organization'] ?: '________________' }} and surrendered to {{ $narrative['surrendered_to'] ?: '________________' }} on {{ $surrenderedOn }} at {{ $narrative['surrendered_at'] ?: '________________' }}.</p>
    <p class="purpose">{{ $purpose }}</p>
    <div class="label">PREPARED BY:</div><div class="personnel">@forelse($certificate['prepared_by'] as $person)<div class="person"><div class="name">{{ $person['full_name'] ?: ' ' }}</div><div class="rank">{{ $person['rank'] }}</div></div>@empty<div class="person"><div class="name"> </div><div class="rank"></div></div>@endforelse</div>
    <div class="label">ATTESTED BY:</div><div class="personnel">@forelse($certificate['attested_by'] as $person)<div class="person"><div class="name">{{ $person['full_name'] ?: ' ' }}</div><div class="rank">{{ $person['rank'] }}</div></div>@empty<div class="person"><div class="name"> </div><div class="rank"></div></div>@endforelse</div>
    <div class="copies"><strong>Copy Furnished:</strong>@foreach($copyFurnished as $line){{ $line }}@if(!$loop->last)<br>@endif @endforeach</div>
</main>
@if($printMode)<script>window.addEventListener('load', () => window.print())</script>@endif
</body>
</html>
