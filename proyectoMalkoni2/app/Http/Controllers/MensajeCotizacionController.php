<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\MensajeCotizacion;
use Illuminate\Http\Request;

class MensajeCotizacionController extends Controller
{
    public function index(int $id)
    {
        $this->authorize($id);

        $mensajes = MensajeCotizacion::where('id_cotizacion', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => $this->formatMensaje($msg));

        return response()->json(['mensajes' => $mensajes]);
    }

    public function store(Request $request, int $id)
    {
        $this->authorize($id);

        $request->validate(['mensaje' => 'required|string|max:2000']);

        $msg = MensajeCotizacion::create([
            'id_cotizacion' => $id,
            'sender_type'   => session('user_role'),
            'sender_id'     => session('user_id'),
            'mensaje'       => trim($request->mensaje),
            'leido'         => false,
        ]);

        return response()->json(['success' => true, 'mensaje' => $this->formatMensaje($msg)]);
    }

    public function marcarLeidos(int $id)
    {
        $this->authorize($id);

        $otherType = session('user_role') === 'cliente' ? 'vendedor' : 'cliente';

        MensajeCotizacion::where('id_cotizacion', $id)
            ->where('sender_type', $otherType)
            ->where('leido', false)
            ->update(['leido' => true]);

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------

    private function authorize(int $id): Cotizacion
    {
        $role   = session('user_role');
        $userId = (int) session('user_id', 0);

        abort_if($userId <= 0, 403);

        $cotizacion = Cotizacion::findOrFail($id);

        if ($role === 'cliente') {
            abort_if((int) $cotizacion->id_personas !== $userId, 403);
        } elseif ($role === 'vendedor') {
            abort_if((int) $cotizacion->id_empleados !== $userId, 403);
        } else {
            abort(403);
        }

        return $cotizacion;
    }

    private function formatMensaje(MensajeCotizacion $msg): array
    {
        return [
            'id'           => $msg->id,
            'sender_type'  => $msg->sender_type,
            'sender_nombre'=> $msg->sender_nombre,
            'mensaje'      => $msg->mensaje,
            'leido'        => $msg->leido,
            'created_at'   => $msg->created_at->timezone('America/Argentina/Buenos_Aires')->format('d/m H:i'),
            'mine'         => $msg->sender_type === session('user_role') && (int) $msg->sender_id === (int) session('user_id'),
        ];
    }
}
