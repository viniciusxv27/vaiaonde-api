<?php

namespace App\Http\Controllers;

use App\Models\ClubBenefit;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Lista todos os benefícios do clube
     */
    public function benefits(Request $request)
    {
        $benefits = ClubBenefit::where('active', true)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'benefits' => $benefits,
        ]);
    }

    /**
     * Retorna informações sobre o clube
     */
    public function info(Request $request)
    {
        return response()->json([
            'success' => true,
            'info' => [
                'name' => 'VáClub',
                'description' => 'O clube de benefícios exclusivos do Vá!Aonde',
                'price_monthly' => 29.90,
                'price_annual' => 299.90,
                'features' => [
                    'Descontos exclusivos em estabelecimentos parceiros',
                    'Vouchers mensais',
                    'Acesso antecipado a novos lugares',
                    'Cashback em visitas',
                    'Eventos exclusivos',
                ],
            ],
        ]);
    }
}
