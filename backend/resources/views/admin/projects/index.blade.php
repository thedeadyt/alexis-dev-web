@extends('admin.layout')
@section('content')
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:22px;font-weight:700;">Projets</h1>
    <a href="/admin/projects/create" class="btn-purple" style="text-decoration:none;padding:8px 18px;border-radius:6px;background:#8B00FF;color:#fff;">+ Nouveau</a>
  </div>
  @if(session('success'))<div style="background:#1a3a1a;border:1px solid #2a6a2a;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#6aff6a;">{{ session('success') }}</div>@endif
  <div class="card">
    @foreach($projects as $p)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #2A2A2A;">
      <div>
        <span style="font-weight:600;">{{ $p->name }}</span>
        <span style="color:#888;margin-left:12px;font-size:13px;">{{ $p->category }} · {{ $p->year }}</span>
        @if(!$p->active)<span style="color:#ff6b6b;font-size:12px;margin-left:8px;">[inactif]</span>@endif
      </div>
      <div style="display:flex;gap:12px;">
        <a href="/admin/projects/{{ $p->id }}/edit" style="color:#8B00FF;text-decoration:none;">Éditer</a>
        <form method="POST" action="/admin/projects/{{ $p->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
          @csrf @method('DELETE')
          <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;">Supprimer</button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
@endsection
