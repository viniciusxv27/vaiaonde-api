<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoulettePrize;

class RoulettePrizeSeeder extends Seeder
{
    public function run(): void
    {
        $prizes = [
            [
                'name' => '10 Pontos',
                'description' => 'Ganhe 10 pontos no seu perfil',
                'type' => 'points',
                'prize_value' => '10 pontos',
                'points_value' => 10,
                'image_url' => '/images/prizes/points.png',
                'color' => '#FFD700',
                'probability' => 30,
                'active' => true,
                'club_exclusive' => false,
            ],
            [
                'name' => '50 Pontos',
                'description' => 'Ganhe 50 pontos no seu perfil',
                'type' => 'points',
                'prize_value' => '50 pontos',
                'points_value' => 50,
                'image_url' => '/images/prizes/points-gold.png',
                'color' => '#FFA500',
                'probability' => 15,
                'active' => true,
                'club_exclusive' => false,
            ],
            [
                'name' => 'R$ 5 de Desconto',
                'description' => 'R$ 5 de desconto em qualquer estabelecimento parceiro',
                'type' => 'discount',
                'prize_value' => 'R$ 5,00',
                'discount_value' => 5.00,
                'image_url' => '/images/prizes/discount-5.png',
                'color' => '#4CAF50',
                'probability' => 20,
                'active' => true,
                'club_exclusive' => false,
            ],
            [
                'name' => 'R$ 10 de Cashback',
                'description' => 'Ganhe R$ 10 em cashback na sua próxima visita',
                'type' => 'cashback',
                'prize_value' => 'R$ 10,00',
                'discount_value' => 10.00,
                'image_url' => '/images/prizes/cashback-10.png',
                'color' => '#2196F3',
                'probability' => 10,
                'active' => true,
                'club_exclusive' => true,
            ],
            [
                'name' => 'R$ 20 de Desconto',
                'description' => 'R$ 20 de desconto em qualquer estabelecimento parceiro',
                'type' => 'discount',
                'prize_value' => 'R$ 20,00',
                'discount_value' => 20.00,
                'image_url' => '/images/prizes/discount-20.png',
                'color' => '#9C27B0',
                'probability' => 8,
                'active' => true,
                'club_exclusive' => true,
            ],
            [
                'name' => 'Item Grátis',
                'description' => 'Ganhe um item grátis no estabelecimento parceiro',
                'type' => 'free_item',
                'prize_value' => '1 item grátis',
                'image_url' => '/images/prizes/free-item.png',
                'color' => '#F44336',
                'probability' => 10,
                'active' => true,
                'club_exclusive' => true,
            ],
            [
                'name' => '100 Pontos VIP',
                'description' => 'Ganhe 100 pontos exclusivos para membros VIP',
                'type' => 'points',
                'prize_value' => '100 pontos',
                'points_value' => 100,
                'image_url' => '/images/prizes/points-vip.png',
                'color' => '#FF9800',
                'probability' => 5,
                'active' => true,
                'club_exclusive' => true,
            ],
            [
                'name' => 'Tente Novamente',
                'description' => 'Que pena! Tente novamente mais tarde',
                'type' => 'points',
                'prize_value' => '0 pontos',
                'points_value' => 0,
                'image_url' => '/images/prizes/try-again.png',
                'color' => '#9E9E9E',
                'probability' => 2,
                'active' => true,
                'club_exclusive' => false,
            ],
        ];

        foreach ($prizes as $prizeData) {
            RoulettePrize::create($prizeData);
        }

        echo "Prêmios da roleta criados com sucesso!\n";
    }
}
