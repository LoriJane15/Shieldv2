<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CDR Draft Preview</title><style>
@page{size:A4;margin:31mm 16mm 28mm 16mm}*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#000;font-size:9.5pt;margin:0}.official-header,.official-footer{position:fixed;left:0;right:0;text-align:center}.official-header{top:-27mm;height:23mm;display:grid;grid-template-columns:18mm 1fr 18mm;align-items:center}.official-header img{height:16mm;width:16mm;object-fit:contain}.official-header strong,.official-header span,.official-footer strong,.official-footer span{display:block}.official-header strong,.official-footer strong{color:#a00000;letter-spacing:.2em}.official-header span,.official-footer span{font-size:7pt}.agila-mark{border:1px solid #777;border-radius:50%;font-size:7pt;font-weight:700;padding:5mm 0}.official-footer{bottom:-24mm;height:20mm}.footer-programs{font-size:5.5pt;margin-bottom:1mm}.toolbar{background:#edf2f7;padding:12px;position:sticky;top:0;z-index:2}.draft-mark{border:2px solid #a00000;color:#a00000;font-size:13pt;font-weight:700;letter-spacing:.08em;margin-bottom:8mm;padding:5px;text-align:center}.cover{text-align:center;min-height:218mm;page-break-after:always}.unit-heading{line-height:1.35}.cover h1{font-size:16pt;margin:20mm 0 9mm}.fr-photo{border:1px solid #000;height:58mm;width:48mm;margin:10mm auto 0;display:flex;align-items:center;justify-content:center}.fr-photo img{height:100%;width:100%;object-fit:contain}.document-section{margin:0 0 6mm}.document-section h2{background:#ddd;border:1px solid #000;font-size:10pt;margin:0;padding:4px;text-transform:uppercase}.document-section.heading-only h2{background:none;border:0;font-size:12pt;text-align:center}.document-section table{border-collapse:collapse;width:100%}th,td{border:1px solid #000;padding:4px;text-align:left;vertical-align:top;overflow-wrap:anywhere}th{font-weight:700}.field-table tbody th{width:32%}.formatted p{margin:0 0 .5em}.formatted ul{margin:.2em 0;padding-left:1.5em}.signatures{display:grid;grid-template-columns:1fr 1fr;gap:16mm;margin-top:18mm;page-break-inside:avoid}.signature{text-align:center}.signature-line{border-top:1px solid #000;margin-top:20mm;padding-top:3px}@media print{.toolbar{display:none!important}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}@media screen{body{background:#d8dee8}.paper{background:#fff;margin:18px auto;max-width:210mm;padding:20mm 16mm;box-shadow:0 2px 14px #777}.official-header,.official-footer{position:static;background:#fff;margin:auto;max-width:210mm}.cover{min-height:auto}}
</style></head><body>
<nav class="toolbar"><a href="{{ route('ib39.cdr.edit',$cdr) }}">Edit draft</a> <button onclick="window.print()">Print</button></nav>
@include('ib39.cdr.partials.official-header') @include('ib39.cdr.partials.official-footer')
<main class="paper"><div class="draft-mark">DRAFT — NOT FINAL</div>
<section class="cover"><div class="unit-heading"><strong>H E A D Q U A R T E R S</strong><br>39TH INFANTRY (SMASH’EM) BATTALION, 10ID, PA<br>Brgy Poblacion, Makilala, Cotabato</div><h1>CUSTODIAL DEBRIEFING REPORT (CDR)</h1><p>Date: {{ data_get($content,'report_date')?:'—' }}</p><p>Reference: {{ data_get($content,'cdr_reference')?:'—' }}</p><p>Subject: {{ data_get($content,'subject_name')?:'—' }} @if(data_get($content,'alias'))({{ data_get($content,'alias') }})@endif</p>
@php
    $slot = $cdr->photos->first(fn ($photo) => $photo->photo_type === \App\Enums\Ib39CdrPhotoType::FrPhoto);
@endphp
<div class="fr-photo" data-cover-fr-photo>@if($slot?->currentVersion)<img src="{{ route('ib39.cdr.photos.show',$slot->currentVersion) }}" alt="FR Photo">@else<span class="fr-photo-placeholder">FR Photo</span>@endif</div></section>
@foreach($orderedBlocks as [$kind,$key])
 @continue($key==='cover'||$key==='signatories')
 @if($kind==='section') @php $section = $sections[$key]; @endphp
  <section class="document-section {{ !$section['fields']?'heading-only':'' }}"><h2>{{ $section['label'] }}</h2>@if($section['fields'])<table class="field-table"><tbody>
  @foreach($section['fields'] as $fieldKey=>$field)
   @continue(in_array($fieldKey,['significant_information','white_area_information','projected_enemy_operations']))
   @php
       $detailFields = ['significant_information_status' => 'significant_information', 'white_area_status' => 'white_area_information', 'projected_enemy_status' => 'projected_enemy_operations'];
       $detail = $detailFields[$fieldKey] ?? null;
   @endphp
   <tr><th>{{ $field['label'] }}</th><td>@if($field['type']==='yes_na_text' && data_get($content,$fieldKey)==='na')N/A @elseif($field['type']==='yes_na_text'){{ data_get($content,$detail)?:'—' }} @elseif($field['type']==='narrative')<div class="formatted">{{ \App\Support\Ib39CdrNarrativeFormatter::render(data_get($content,$fieldKey)) }}</div>@else<span style="white-space:pre-wrap">{{ data_get($content,$fieldKey)?:'—' }}</span>@endif</td></tr>
  @endforeach</tbody></table>@endif</section>
 @else @php
     $section = $repeatableSections[$key];
     $rows = data_get($content, $key, []);
     $na = isset($section['status_key']) && data_get($content, $section['status_key']) === 'na';
 @endphp
  <section class="document-section"><h2>{{ $section['label'] }}</h2>@if($na)<p>N/A</p>@else<table><thead><tr>@foreach($section['columns'] as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)<tr>@foreach($section['columns'] as $column=>$label)<td style="white-space:pre-wrap">{{ data_get($row,$column)?:'—' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($section['columns']) }}">—</td></tr>@endforelse</tbody></table>@endif</section>
 @endif
@endforeach
<section class="signatures"><div class="signature"><div class="signature-line"><strong>{{ data_get($content,'debriefer_name')?:'—' }}</strong><br>Debriefer</div></div><div class="signature"><div class="signature-line"><strong>{{ data_get($content,'approving_officer_name')?:'—' }}</strong><br>Commanding Officer</div></div></section>
</main>@if($autoPrint)<script>window.addEventListener('load',function(){window.print()})</script>@endif</body></html>
