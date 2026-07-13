<?php

namespace App\Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RegistrationOptionsController extends Controller
{
    /**
     * Fixed option lists for the Lodge self-registration form (Potência,
     * Rito, Tipo, Grau), so the mobile app never hardcodes them.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'potencias' => config('lodge.potencias'),
            'ritos' => config('lodge.ritos'),
            'tipos' => config('lodge.tipos'),
            'graus' => config('lodge.graus'),
        ]);
    }
}
