<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:production';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otimiza a aplicação para produção (limpa e recria caches)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Otimizando aplicação para produção...');
        $this->newLine();

        // Limpar caches antigos
        $this->warn('📦 Passo 1/2: Limpando caches antigos...');
        $this->call('cache:clear-all');
        
        $this->newLine();
        $this->warn('🔨 Passo 2/2: Recriando caches otimizados...');
        $this->newLine();

        // Config cache
        $this->call('config:cache');
        $this->info('✅ Cache de configuração criado');

        // Route cache
        $this->call('route:cache');
        $this->info('✅ Cache de rotas criado');

        // View cache
        $this->call('view:cache');
        $this->info('✅ Cache de views criado');

        // Event cache (Laravel 11+)
        if (method_exists($this, 'call') && $this->hasCommand('event:cache')) {
            $this->call('event:cache');
            $this->info('✅ Cache de eventos criado');
        }

        $this->newLine();
        $this->info('✨ Aplicação otimizada para produção!');
        $this->info('🎯 Performance máxima ativada!');
        
        return 0;
    }

    /**
     * Check if a command exists
     */
    private function hasCommand($command)
    {
        try {
            $this->getApplication()->find($command);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
