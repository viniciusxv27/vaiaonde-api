<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartnerSubscriptionPlan;

class PartnerSubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuito',
                'price' => 0.00,
                'description' => 'Cadastro básico sem benefícios adicionais',
                'features' => [
                    'Cadastro do negócio no app',
                    'Aparece em buscas gerais',
                    'Sem relatórios'
                ],
                'can_launch_promotions' => false,
                'appears_in_top' => false,
                'professional_videos_per_month' => 0,
                'has_analytics' => false,
                'is_active' => true
            ],
            [
                'name' => 'Plano Local',
                'price' => 99.00,
                'description' => 'Ideal para negócios locais que querem mais visibilidade',
                'features' => [
                    'Aparece em buscas com destaque local',
                    'Relatórios básicos de visualizações',
                    'Estatísticas de menções',
                    'Suporte por email'
                ],
                'can_launch_promotions' => false,
                'appears_in_top' => false,
                'professional_videos_per_month' => 0,
                'has_analytics' => true,
                'is_active' => true
            ],
            [
                'name' => 'Plano Regional',
                'price' => 249.00,
                'description' => 'Para negócios que querem alcançar toda a região',
                'features' => [
                    'Todos os benefícios do Plano Local',
                    'Pode lançar promoções no clube de benefícios',
                    'Aparece em buscas regionais',
                    'Relatórios avançados',
                    'Suporte prioritário'
                ],
                'can_launch_promotions' => true,
                'appears_in_top' => false,
                'professional_videos_per_month' => 0,
                'has_analytics' => true,
                'is_active' => true
            ],
            [
                'name' => 'Plano Destaque',
                'price' => 499.00,
                'description' => 'Máxima visibilidade para seu negócio',
                'features' => [
                    'Todos os benefícios do Plano Regional',
                    'Aparece no topo da aba de vídeos',
                    '1 vídeo profissional mensal com influenciador',
                    'Relatórios completos em tempo real',
                    'Suporte VIP 24/7',
                    'Gerente de conta dedicado'
                ],
                'can_launch_promotions' => true,
                'appears_in_top' => true,
                'professional_videos_per_month' => 1,
                'has_analytics' => true,
                'is_active' => true
            ]
        ];

        foreach ($plans as $plan) {
            PartnerSubscriptionPlan::create($plan);
        }
    }
}
