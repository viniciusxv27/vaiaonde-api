<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClubBenefit;

class ClubBenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $benefits = [
            [
                'title' => 'Descontos Exclusivos',
                'description' => 'Até 50% de desconto em estabelecimentos parceiros',
                'icon' => 'discount',
                'order' => 1,
                'active' => true,
            ],
            [
                'title' => 'Vouchers Mensais',
                'description' => 'Receba vouchers exclusivos todo mês',
                'icon' => 'voucher',
                'order' => 2,
                'active' => true,
            ],
            [
                'title' => 'Acesso Antecipado',
                'description' => 'Seja o primeiro a conhecer novos estabelecimentos',
                'icon' => 'star',
                'order' => 3,
                'active' => true,
            ],
            [
                'title' => 'Cashback',
                'description' => 'Ganhe cashback em todas as suas visitas',
                'icon' => 'money',
                'order' => 4,
                'active' => true,
            ],
            [
                'title' => 'Eventos Exclusivos',
                'description' => 'Participe de eventos exclusivos para membros',
                'icon' => 'event',
                'order' => 5,
                'active' => true,
            ],
        ];

        foreach ($benefits as $benefit) {
            ClubBenefit::updateOrCreate(
                ['title' => $benefit['title']],
                $benefit
            );
        }

        echo "Benefícios do clube criados com sucesso!\n";
    }
}
