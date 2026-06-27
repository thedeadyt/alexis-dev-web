@extends('admin.layout')
@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;">Témoignages</h1>
    <a href="/admin/testimonials/create" style="text-decoration:none;padding:8px 18px;border-radius:6px;background:#8B00FF;color:#fff;">+ Nouveau</a>
  </div>
  @if(session('success'))<div style="background:#1a3a1a;border:1px solid #2a6a2a;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#6aff6a;">{{ session('success') }}</div>@endif
  <div class="card">
    @foreach($testimonials as $t)
    <div style="padding:12px 0;border-bottom:1px solid #2A2A2A;">
      <div style="display:flex;justify-content:space-between;">
        <div>
          <span style="font-weight:600;">{{ $t->author }}</span>
          @if(!$t->active)<span style="color:#ff6b6b;font-size:12px;margin-left:8px;">[inactif]</span>@endif
        </div>
        <div style="display:flex;gap:12px;">
          <a href="/admin/testimonials/{{ $t->id }}/edit" style="color:#8B00FF;text-decoration:none;">Éditer</a>
          <form method="POST" action="/admin/testimonials/{{ $t->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;">Supprimer</button>
          </form>
        </div>
      </div>
      <div style="color:#888;font-size:13px;margin-top:4px;">{{ $t->role }}</div>
      <div style="color:#aaa;margin-top:6px;font-style:italic;">"{{ Str::limit($t->quote, 100) }}"</div>
    </div>
    @endforeach
  </div>
@endsection
