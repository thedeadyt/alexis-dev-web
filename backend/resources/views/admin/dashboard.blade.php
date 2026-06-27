@extends('admin.layout')
@section('content')
  <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">Dashboard</h1>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;">
    @foreach([['Contacts', $contactsCount, '/admin/contacts'], ['Projets', $projectsCount, '/admin/projects'], ['Services', $servicesCount, '/admin/services'], ['Témoignages', $testimonialsCount, '/admin/testimonials']] as [$label, $count, $href])
    <a href="{{ $href }}" style="text-decoration:none;" class="card">
      <div style="font-size:32px;font-weight:700;color:#8B00FF;">{{ $count }}</div>
      <div style="color:#888;margin-top:4px;">{{ $label }}</div>
    </a>
    @endforeach
  </div>
  <div class="card">
    <h2 style="font-size:16px;font-weight:600;margin-bottom:16px;">Dernières demandes</h2>
    @forelse($latestContacts as $c)
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #2A2A2A;">
        <span>{{ $c->first_name }} {{ $c->last_name }} — {{ $c->email }}</span>
        <span style="color:#888;font-size:13px;">{{ $c->created_at->format('d/m/Y H:i') }}</span>
      </div>
    @empty
      <p style="color:#888;">Aucune demande pour l'instant.</p>
    @endforelse
  </div>
@endsection
