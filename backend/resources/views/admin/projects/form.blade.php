@extends('admin.layout')
@section('content')
  <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">{{ $project ? 'Éditer' : 'Nouveau' }} projet</h1>
  <form method="POST" action="{{ $project ? '/admin/projects/'.$project->id : '/admin/projects' }}" class="card">
    @csrf
    @if($project) @method('PUT') @endif
    @if($errors->any())<div style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    @foreach([['slug','Slug (URL)','text'],['name','Nom','text'],['client','Client','text'],['year','Année','text']] as [$field, $label, $type])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $label }}</label>
      <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $project?->{$field}) }}" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>
    @endforeach

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Catégorie</label>
      <select name="category" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
        @foreach(['Sites vitrines','Applications','Infra'] as $cat)
          <option value="{{ $cat }}" {{ old('category', $project?->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
    </div>

    @foreach([['summary','Résumé (une ligne)'],['full_text','Texte complet (un paragraphe par ligne)'],['tech','Technologies (une par ligne)'],['rendered','Livrables (un par ligne)']] as [$field, $label])
    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">{{ $label }}</label>
      @php
        $val = old($field, $project ? (is_array($project->{$field}) ? implode("\n", $project->{$field}) : $project->{$field}) : '');
      @endphp
      <textarea name="{{ $field }}" rows="4" style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">{{ $val }}</textarea>
    </div>
    @endforeach

    <div style="margin-bottom:16px;">
      <label style="display:block;color:#888;font-size:13px;margin-bottom:6px;">Ordre</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $project?->sort_order ?? 0) }}" style="width:80px;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;">
    </div>

    <label style="display:flex;align-items:center;gap:8px;margin-bottom:24px;cursor:pointer;">
      <input type="checkbox" name="active" value="1" {{ old('active', $project?->active ?? true) ? 'checked' : '' }}>
      <span style="color:#888;">Actif</span>
    </label>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn-purple">Enregistrer</button>
      <a href="/admin/projects" style="color:#888;text-decoration:none;padding:6px 0;">Annuler</a>
    </div>
  </form>
@endsection
