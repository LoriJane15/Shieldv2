@extends('layouts.skydash-v')
@section('title','Edit CDR Draft') @section('heading','Edit Custodial Debriefing Report Draft')
@push('styles')<style>.cdr-editor fieldset{border:1px solid #e2e8f0;border-radius:.5rem;margin-bottom:1.25rem;padding:1rem}.cdr-editor legend{font-size:1rem;font-weight:700;padding:0 .4rem;width:auto}.repeat-table{min-width:720px}.photo-preview{height:220px;max-width:100%;object-fit:contain}.format-tools button{margin-right:.3rem}</style>@endpush
@section('content')
<div class="cdr-editor">
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>The draft was not saved.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="d-flex justify-content-between mb-3"><div><strong>Status: {{ $cdr->status->value }}</strong><small class="d-block text-muted">The official header, footer, logos, labels, and signatory titles are fixed.</small></div><div><a class="btn btn-outline-primary" href="{{ route('ib39.cdr.preview',$cdr) }}">Preview</a> <a class="btn btn-light" href="{{ route('ib39.cdr.show',$cdr) }}">Workspace</a></div></div>
@php $draft = old('content', array_replace($defaultContent, $cdr->form?->content ?? [])); @endphp
@php
    $photoSlot = $cdr->photos->first(fn ($photo) => $photo->photo_type === \App\Enums\Ib39CdrPhotoType::FrPhoto);
@endphp
<form id="fr-photo-upload" method="POST" enctype="multipart/form-data" action="{{ route('ib39.cdr.photos.store',$cdr) }}">@csrf</form>
<form method="POST" action="{{ route('ib39.cdr.update',$cdr) }}">@csrf @method('PUT')
@foreach($orderedBlocks as [$kind,$key])
 @if($kind==='section') @php $section = $sections[$key]; @endphp
 <fieldset data-section="{{ $key }}"><legend>{{ $section['label'] }}</legend>@if(!$section['fields'])<p class="text-muted mb-0">Official section heading</p>@else<div class="row">
 @foreach($section['fields'] as $fieldKey=>$field)<div class="col-md-6 mb-3" @if(in_array($fieldKey,['significant_information','white_area_information','projected_enemy_operations'])) data-text-detail="{{ $fieldKey }}" @endif><label for="cdr-{{ $fieldKey }}">{{ $field['label'] }}</label>
 @if($field['type']==='yes_na_text')<select id="cdr-{{ $fieldKey }}" class="form-control" name="content[{{ $fieldKey }}]" data-text-status><option value="">Select</option><option value="yes" @selected(data_get($draft,$fieldKey)==='yes')>Yes</option><option value="na" @selected(data_get($draft,$fieldKey)==='na')>N/A</option></select>
 @elseif(in_array($field['type'],['textarea','narrative']))@if($field['type']==='narrative')<div class="format-tools"><button type="button" class="btn btn-sm btn-light" data-format="bold">Bold</button><button type="button" class="btn btn-sm btn-light" data-format="bullet">Bullet</button></div><small class="text-muted">Only **bold**, bullets, and line breaks are rendered.</small>@endif<textarea id="cdr-{{ $fieldKey }}" class="form-control" rows="4" maxlength="{{ $field['max'] }}" name="content[{{ $fieldKey }}]">{{ data_get($draft,$fieldKey) }}</textarea>
 @else<input id="cdr-{{ $fieldKey }}" class="form-control" type="{{ $field['type']==='date'?'date':'text' }}" maxlength="{{ $field['max'] }}" name="content[{{ $fieldKey }}]" value="{{ data_get($draft,$fieldKey) }}">@endif</div>@endforeach</div>@endif</fieldset>
 @if($key === 'cover')
 <fieldset data-section="fr-photo"><legend>FR Photo</legend>@if($photoSlot?->currentVersion)<img class="photo-preview d-block mb-2" alt="Current FR Photo" src="{{ route('ib39.cdr.photos.show',$photoSlot->currentVersion) }}">@endif<input form="fr-photo-upload" type="hidden" name="photo_type" value="fr_photo"><label for="fr-photo">FR Photo</label><small class="d-block">JPEG or PNG, maximum 5 MB</small><input form="fr-photo-upload" id="fr-photo" type="file" name="photo" accept="image/jpeg,image/png" required><button form="fr-photo-upload" class="btn btn-sm btn-outline-primary" type="submit">{{ $photoSlot?->currentVersion?'Replace':'Save' }} FR Photo</button></fieldset>
 @endif
 @else @php
     $section = $repeatableSections[$key];
     $rows = data_get($draft, $key, []);
     $rows = count($rows) ? $rows : [[]];
     $statusKey = $section['status_key'] ?? null;
 @endphp
 <fieldset data-repeatable="{{ $key }}"><legend>{{ $section['label'] }}</legend>@if($statusKey)<label for="cdr-{{ $statusKey }}">Applicability</label><select id="cdr-{{ $statusKey }}" class="form-control mb-3" name="content[{{ $statusKey }}]" data-applicability><option value="">Select</option><option value="yes" @selected(data_get($draft,$statusKey)==='yes')>Yes</option><option value="na" @selected(data_get($draft,$statusKey)==='na')>N/A</option></select>@endif
 <div data-table-details><div class="table-responsive"><table class="table table-bordered repeat-table"><thead><tr>@foreach($section['columns'] as $label)<th>{{ $label }}</th>@endforeach<th>Action</th></tr></thead><tbody>@foreach($rows as $i=>$row)<tr>@foreach($section['columns'] as $column=>$label)<td><textarea class="form-control" rows="2" maxlength="10000" name="content[{{ $key }}][{{ $i }}][{{ $column }}]">{{ data_get($row,$column) }}</textarea></td>@endforeach<td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Remove</button></td></tr>@endforeach</tbody></table></div><button type="button" class="btn btn-sm btn-outline-secondary" data-add-row>Add row</button></div></fieldset>
 @endif
@endforeach
<button class="btn btn-primary" type="submit">Save draft</button></form>
</div>@endsection
@push('scripts')<script>
document.querySelectorAll('[data-repeatable]').forEach(function(s){function ix(){s.querySelectorAll('tbody tr').forEach(function(r,i){r.querySelectorAll('textarea').forEach(function(f){f.name=f.name.replace(/\[\d+\]/,'['+i+']')})})}s.querySelector('[data-add-row]').onclick=function(){var b=s.querySelector('tbody');if(b.rows.length>={{ \App\Support\Ib39CdrFormSchema::MAX_ROWS }})return;var r=b.rows[0].cloneNode(true);r.querySelectorAll('textarea').forEach(function(f){f.value=''});b.appendChild(r);ix()};s.onclick=function(e){if(!e.target.matches('[data-remove-row]'))return;var b=s.querySelector('tbody');if(b.rows.length>1){e.target.closest('tr').remove();ix()}else b.querySelectorAll('textarea').forEach(function(f){f.value=''})};var a=s.querySelector('[data-applicability]');if(a){function toggle(){var off=a.value==='na',d=s.querySelector('[data-table-details]');d.hidden=off;d.querySelectorAll('textarea,button').forEach(function(f){f.disabled=off})}a.onchange=toggle;toggle()}});
document.querySelectorAll('[data-text-status]').forEach(function(s){var stem=s.id.replace('cdr-','').replace('_status',''),d=document.querySelector('[data-text-detail="'+stem+'"]');if(!d)return;function toggle(){var off=s.value==='na';d.hidden=off;d.querySelectorAll('textarea').forEach(function(f){f.disabled=off})}s.onchange=toggle;toggle()});
document.querySelectorAll('[data-format]').forEach(function(b){b.onclick=function(){var a=b.closest('.mb-3').querySelector('textarea'),x=a.selectionStart,y=a.selectionEnd,t=a.value.slice(x,y);a.setRangeText(b.dataset.format==='bold'?'**'+t+'**':(x&&a.value[x-1]!=='\n'?'\n':'')+'- '+t,x,y,'end');a.focus()}});
</script>@endpush
