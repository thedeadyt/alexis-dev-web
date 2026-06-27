@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">{{ $testimonial ? 'Éditer' : 'Nouveau' }} témoignage</h1>
  <form method="POST" action="{{ $testimonial ? '/admin/testimonials/'.$testimonial->id : '/admin/testimonials' }}" class="card">
    @csrf
    @if($testimonial) @method('PUT') @endif
    @if($errors->any())<div style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Citation</label>
      <textarea name="quote" rows="4" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">{{ old('quote', $testimonial?->quote) }}</textarea>
    </div>
    @foreach([['author','Auteur'],['role','Rôle (ex: Gérante, Boulangerie Martin)']] as [$f, $l])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $l }}</label>
      <input type="text" name="{{ $f }}" value="{{ old($f, $testimonial?->{$f}) }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    @endforeach
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Ordre</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}" style="width:80px;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    <label style="display:flex;align-items:center;gap:8px;margin-bottom:24px;cursor:pointer;">
      <input type="checkbox" name="active" value="1" {{ old('active', $testimonial?->active ?? true) ? 'checked' : '' }}>
      <span style="color:#888;">Actif</span>
    </label>
    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn-purple">Enregistrer</button>
      <a href="/admin/testimonials" style="color:#888;text-decoration:none;padding:6px 0;">Annuler</a>
    </div>
  </form>
@endsection
