<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body { background:#111111; color:#F9F9F9; display:flex; align-items:center; justify-content:center; min-height:100vh; }</style>
</head>
<body>
  <div style="background:#1A1A1A;border:1px solid #2A2A2A;border-radius:12px;padding:40px;width:360px;">
    <h1 style="font-size:22px;font-weight:700;margin-bottom:24px;">alexis dev web <span style="color:#8B00FF;">// admin</span></h1>
    @if($errors->any())
      <p style="color:#ff6b6b;margin-bottom:16px;">{{ $errors->first() }}</p>
    @endif
    <form method="POST" action="/admin/login">
      @csrf
      <div style="margin-bottom:16px;">
        <label style="display:block;margin-bottom:6px;color:#888;font-size:13px;">Email</label>
        <input type="email" name="email" required style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;outline:none;">
      </div>
      <div style="margin-bottom:24px;">
        <label style="display:block;margin-bottom:6px;color:#888;font-size:13px;">Mot de passe</label>
        <input type="password" name="password" required style="width:100%;background:#111;border:1px solid #2A2A2A;border-radius:6px;padding:10px;color:#F9F9F9;outline:none;">
      </div>
      <button type="submit" style="width:100%;background:#8B00FF;color:#fff;border:none;border-radius:6px;padding:12px;font-size:15px;cursor:pointer;">Se connecter</button>
    </form>
  </div>
</body>
</html>
