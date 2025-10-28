<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'VáClub Mensal',
                'slug' => 'monthly',
                'description' => 'Acesso completo aos benefícios do VáClub com pagamento mensal',
                'price' => 29.90,
                'original_price' => null,
                'period' => 'month',
                'period_count' => 1,
                'stripe_price_id' => env('STRIPE_MONTHLY_PRICE_ID', 'price_monthly'),
                'features' => [
                    'Descontos exclusivos em estabelecimentos parceiros',
                    'Vouchers mensais',
                    '1 giro na roleta por mês',
                    'Acesso a eventos exclusivos',
                    'Suporte prioritário',
                ],
                'roulette_spins_per_month' => 1,
                'priority_support' => 1,
                'active' => true,
                'is_popular' => false,
                'order' => 1,
            ],
            [
                'name' => 'VáClub Trimestral',
                'slug' => 'quarterly',
                'description' => 'Plano trimestral com desconto especial',
                'price' => 79.90,
                'original_price' => 89.70,
                'period' => 'quarter',
                'period_count' => 3,
                'stripe_price_id' => env('STRIPE_QUARTERLY_PRICE_ID', 'price_quarterly'),
                'features' => [
                    'Tudo do plano mensal',
                    '3 giros na roleta por mês',
                    'Economia de 11% vs mensal',
                    'Cashback em visitas',
                ],
                'roulette_spins_per_month' => 3,
                'priority_support' => 2,
                'active' => true,
                'is_popular' => false,
                'order' => 2,
            ],
            [
                'name' => 'VáClub Anual',
                'slug' => 'annual',
                'description' => 'O melhor custo-benefício! Plano anual com máximo desconto',
                'price' => 299.90,
                'original_price' => 358.80,
                'period' => 'year',
                'period_count' => 12,
                'stripe_price_id' => env('STRIPE_ANNUAL_PRICE_ID', 'price_annual'),
                'features' => [
                    'Tudo dos planos anteriores',
                    '5 giros na roleta por mês',
                    'Economia de 17% vs mensal',
                    'Bônus de aniversário',
                    'Acesso antecipado a novidades',
                    'Suporte VIP',
                ],
                'roulette_spins_per_month' => 5,
                'priority_support' => 3,
                'active' => true,
                'is_popular' => true,
                'order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        echo "Planos de assinatura criados com sucesso!\n";
    }
}
