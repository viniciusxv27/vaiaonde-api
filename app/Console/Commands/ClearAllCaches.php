<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAllCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa todos os caches do Laravel (config, route, view, cache)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Limpando todos os caches...');
        $this->newLine();

        // Config cache
        $this->call('config:clear');
        $this->info('✅ Cache de configuração limpo');

        // Route cache
        $this->call('route:clear');
        $this->info('✅ Cache de rotas limpo');

        // View cache
        $this->call('view:clear');
        $this->info('✅ Cache de views limpo');

        // Application cache
        $this->call('cache:clear');
        $this->info('✅ Cache da aplicação limpo');

        // Clear compiled
        $this->call('clear-compiled');
        $this->info('✅ Cache compilado limpo');

        $this->newLine();
        $this->info('✨ Todos os caches foram limpos com sucesso!');
        
        return 0;
    }
}
