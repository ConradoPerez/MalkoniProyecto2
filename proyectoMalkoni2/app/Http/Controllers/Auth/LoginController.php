<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $password = $credentials['password'];

        $persona = Persona::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($persona) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'El acceso de clientes es exclusivo a través del panel principal de Malkoni Online',
                ]);
        }

        $empleado = Empleado::query()->with('rol')->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($empleado && $this->matchesStoredPassword($empleado, $password)) {
            $role = strtolower((string) optional($empleado->rol)->nombre);
            $mappedRole = match ($role) {
                'supervisor' => 'supervisor',
                'vendedor' => 'vendedor',
                'admin' => 'supervisor',
                default => 'vendedor',
            };

            $request->session()->regenerate();
            session([
                'user_id' => (int) $empleado->id_empleado,
                'user_role' => $mappedRole,
            ]);

            return $mappedRole === 'supervisor'
                ? redirect()->route('dashboard')
                : redirect()->route('vendedor.dashboard');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Credenciales inválidas.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $role = (string) session('user_role', '');

        if ($role === 'cliente') {
            session()->flush();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('https://online.malkoni.com.ar/public/login.php');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function ssoBridge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'persona_id' => ['required', 'integer', 'min:1'],
            'cotizacion_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $persona = Persona::query()->findOrFail($data['persona_id']);

        $request->session()->regenerate();
        session([
            'user_id' => (int) $persona->id_persona,
            'user_role' => 'cliente',
        ]);

        if (!empty($data['cotizacion_id'])) {
            $cotizacion = Cotizacion::query()
                ->where('id', $data['cotizacion_id'])
                ->where('id_personas', $persona->id_persona)
                ->firstOrFail();

            // Si la cotización ya tiene vendedor asignado o ya no está en estado "Nuevo",
            // redirigir al dashboard del cliente con una advertencia en la sesión.
            if ($cotizacion->id_empleados !== null || $cotizacion->estado !== 'Nuevo') {
                return redirect()->route('cliente.dashboard')->with('warning_cotizacion_existente', [
                    'id' => $cotizacion->id,
                    'numero' => $cotizacion->numero,
                ]);
            }

            return redirect()->route('cliente.nueva_cotizacion', [
                'cotizacion_id' => $data['cotizacion_id'],
            ]);
        }

        return redirect()->route('cliente.dashboard');
    }

    private function matchesStoredPassword(object $model, string $plain): bool
    {
        $hash = $this->extractPasswordHash($model);
        if (!$hash) {
            return false;
        }

        if (Hash::check($plain, $hash)) {
            return true;
        }

        if (password_verify($plain, $hash)) {
            return true;
        }

        return hash_equals($hash, md5($plain))
            || hash_equals($hash, sha1($plain))
            || hash_equals($hash, $plain);
    }

    private function extractPasswordHash(object $model): ?string
    {
        foreach (['password', 'clave', 'contrasena', 'pass'] as $field) {
            $value = $model->{$field} ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
