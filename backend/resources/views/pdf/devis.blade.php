<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; color: #1a1a1a; font-size: 13px; margin: 0; padding: 0; }
    .header { background: #111111; color: #F9F9F9; padding: 30px 40px; }
    .header h1 { font-size: 28px; margin: 0 0 4px; }
    .header span { color: #8B00FF; }
    .content { padding: 40px; }
    .section { margin-bottom: 28px; }
    .label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .value { font-size: 15px; font-weight: 600; }
    .grid { display: flex; gap: 40px; flex-wrap: wrap; }
    .grid-item { flex: 1; min-width: 180px; }
    .divider { border: none; border-top: 1px solid #eee; margin: 24px 0; }
    .message-box { background: #f9f9f9; border-left: 3px solid #8B00FF; padding: 16px; border-radius: 4px; }
    .footer { margin-top: 48px; padding-top: 16px; border-top: 1px solid #eee; color: #888; font-size: 11px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>alexis dev web<span>.</span></h1>
    <div style="color: #888; font-size: 12px; margin-top: 4px;">Récapitulatif de demande</div>
  </div>

  <div class="content">
    <div class="section">
      <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
          <div class="label">Référence</div>
          <div class="value">#{{ str_pad($contact->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div style="text-align:right;">
          <div class="label">Date de la demande</div>
          <div class="value">{{ $contact->created_at->format('d/m/Y') }}</div>
        </div>
      </div>
    </div>

    <hr class="divider">

    <div class="section">
      <div class="label" style="margin-bottom: 12px;">Informations client</div>
      <div class="grid">
        <div class="grid-item">
          <div class="label">Nom</div>
          <div class="value">{{ $contact->first_name }} {{ $contact->last_name }}</div>
        </div>
        <div class="grid-item">
          <div class="label">Email</div>
          <div class="value">{{ $contact->email }}</div>
        </div>
        @if($contact->phone)
        <div class="grid-item">
          <div class="label">Téléphone</div>
          <div class="value">{{ $contact->phone }}</div>
        </div>
        @endif
      </div>
    </div>

    <hr class="divider">

    <div class="section">
      <div class="label" style="margin-bottom: 12px;">Détails du projet</div>
      <div class="grid">
        <div class="grid-item">
          <div class="label">Type de prestation</div>
          <div class="value">{{ $contact->type }}</div>
        </div>
        <div class="grid-item">
          <div class="label">Budget estimé</div>
          <div class="value" style="color: #8B00FF;">{{ $contact->budget }}</div>
        </div>
      </div>
    </div>

    @if($contact->message)
    <hr class="divider">
    <div class="section">
      <div class="label" style="margin-bottom: 12px;">Message</div>
      <div class="message-box">{{ $contact->message }}</div>
    </div>
    @endif

    <div class="footer">
      <p>alexis dev web · rodriguesdosreisalexis@gmail.com · alexis-rodrigues.fr</p>
      <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
  </div>
</body>
</html>
