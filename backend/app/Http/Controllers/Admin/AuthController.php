<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller {
    public function showLogin(): \Illuminate\View\View {
        return view('admin.login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user && password_verify($request->password, $user->password)) {
            session(['admin_logged_in' => true, 'admin_name' => $user->name]);
            return redirect('/admin');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.']);
    }

    public function logout(): \Illuminate\Http\RedirectResponse {
        session()->forget(['admin_logged_in', 'admin_name']);
        return redirect('/admin/login');
    }
}
