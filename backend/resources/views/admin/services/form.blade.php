@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">{{ $service ? 'Éditer' : 'Nouveau' }} service</h1>
  <form method="POST" action="{{ $service ? '/admin/services/'.$service->id : '/admin/services' }}" class="card">
    @csrf
    @if($service) @method('PUT') @endif
    @if($errors->any())<div style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    @foreach([['slug','Slug'],['label','Label code (ex: // vitrine/)'],['title','Titre'],['sub','Sous-titre'],['price','Prix (ex: À partir de 800€)']] as [$f, $l])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $l }}</label>
      <input type="text" name="{{ $f }}" value="{{ old($f, $service?->{$f}) }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    @endforeach

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Description</label>
      <textarea name="body" rows="3" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">{{ old('body', $service?->body) }}</textarea>
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Tags (séparés par virgule)</label>
      <input type="text" name="tags" value="{{ old('tags', $service ? implode(', ', $service->tags) : '') }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Ordre</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $service?->sort_order ?? 0) }}" style="width:80px;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>

    <label style="display:flex;align-items:center;gap:8px;margin-bottom:24px;cursor:pointer;">
      <input type="checkbox" name="active" value="1" {{ old('active', $service?->active ?? true) ? 'checked' : '' }}>
      <span style="color:#888;">Actif</span>
    </label>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn-purple">Enregistrer</button>
      <a href="/admin/services" style="color:#888;text-decoration:none;padding:6px 0;">Annuler</a>
    </div>
  </form>
@endsection
