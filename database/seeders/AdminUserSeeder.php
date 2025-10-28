<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se o usuário já existe
        $existingUser = User::where('email', 'vinicius8cm@gmail.com')->first();

        if ($existingUser) {
            echo "Usuário admin já existe.\n";
            return;
        }

        User::create([
            'name' => 'Vinicius Admin',
            'email' => 'vinicius8cm@gmail.com',
            'password' => Hash::make('senha123'),
            'phone' => '',
            'birthday' => '0',
            'is_admin' => true,
            'subscription' => true,
            'score' => 0,
            'economy' => 0,
            'promocode' => '1',
            'ticket_count' => 0,
        ]);

        echo "Usuário admin criado com sucesso!\n";
        echo "Email: vinicius8cm@gmail.com\n";
        echo "Senha: senha123\n";
    }
}
