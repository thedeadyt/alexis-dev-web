<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — alexis dev web</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #111111; color: #F9F9F9; font-family: system-ui, sans-serif; }
    .accent { color: #8B00FF; }
    .btn-purple { background: #8B00FF; color: #fff; padding: 6px 16px; border-radius: 6px; border: none; cursor: pointer; }
    .btn-purple:hover { background: #7000cc; }
    .card { background: #1A1A1A; border: 1px solid #2A2A2A; border-radius: 8px; padding: 20px; }
  </style>
</head>
<body>
  <nav style="background:#161616;border-bottom:1px solid #2A2A2A;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-weight:700;font-size:18px;">alexis dev web <span class="accent">// admin</span></span>
    <div style="display:flex;gap:16px;align-items:center;">
      <a href="/admin" style="color:#888;text-decoration:none;">Dashboard</a>
      <a href="/admin/projects" style="color:#888;text-decoration:none;">Projets</a>
      <a href="/admin/services" style="color:#888;text-decoration:none;">Services</a>
      <a href="/admin/testimonials" style="color:#888;text-decoration:none;">Témoignages</a>
      <a href="/admin/contacts" style="color:#888;text-decoration:none;">Contacts</a>
      <form method="POST" action="/admin/logout" style="display:inline;">
        @csrf
        <button type="submit" style="background:none;border:none;color:#888;cursor:pointer;">Déconnexion</button>
      </form>
    </div>
  </nav>
  <main style="max-width:1100px;margin:40px auto;padding:0 24px;">
    @yield('content')
  </main>
</body>
</html>
