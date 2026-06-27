@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">Demandes de contact</h1>
  @if(session('success'))<div style="background:#1a3a1a;border:1px solid #2a6a2a;border-radius:6px;padding:12px 16px;margin-bottom:16px;color:#6aff6a;">{{ session('success') }}</div>@endif
  <div class="card">
    @forelse($contacts as $c)
    <div style="padding:16px 0;border-bottom:1px solid #2A2A2A;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <span style="font-weight:600;">{{ $c->first_name }} {{ $c->last_name }}</span>
          <span style="color:#888;margin-left:12px;">{{ $c->email }}</span>
          @if($c->phone)<span style="color:#888;margin-left:8px;">· {{ $c->phone }}</span>@endif
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
          <span style="color:#888;font-size:13px;">{{ $c->created_at->format('d/m/Y H:i') }}</span>
          <a href="/admin/contacts/{{ $c->id }}/pdf" style="color:#8B00FF;text-decoration:none;font-size:13px;">PDF</a>
          <form method="POST" action="/admin/contacts/{{ $c->id }}" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
            @csrf @method('DELETE')
            <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:13px;">Supprimer</button>
          </form>
        </div>
      </div>
      <div style="margin-top:6px;display:flex;gap:16px;">
        <span style="background:#1a1a2e;border:1px solid #2A2A2A;border-radius:4px;padding:2px 8px;font-size:12px;color:#8B00FF;">{{ $c->type }}</span>
        <span style="color:#888;font-size:13px;">Budget : {{ $c->budget }}</span>
      </div>
      @if($c->message)
      <p style="color:#aaa;font-size:13px;margin-top:8px;">{{ Str::limit($c->message, 150) }}</p>
      @endif
    </div>
    @empty
    <p style="color:#888;padding:16px 0;">Aucune demande pour l'instant.</p>
    @endforelse
  </div>
  <div style="margin-top:16px;">{{ $contacts->links() }}</div>
@endsection
