<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Em produção, verificar se o cache de views está funcional
        if ($this->app->environment('production')) {
            $this->ensureViewCacheIsValid();
        }
    }
    
    /**
     * Verificar se o cache de views está válido
     */
    private function ensureViewCacheIsValid(): void
    {
        try {
            $viewCachePath = storage_path('framework/views');
            
            // Se a pasta de cache não existe, criar
            if (!file_exists($viewCachePath)) {
                mkdir($viewCachePath, 0755, true);
                return;
            }
            
            // Verificar se há arquivos compilados
            $compiledFiles = glob($viewCachePath . '/*.php');
            
            // Se não há arquivos ou pasta está vazia, compilar as views principais
            if (empty($compiledFiles)) {
                \Log::info('Cache de views vazio - views serão compiladas sob demanda');
            }
            
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar cache de views: ' . $e->getMessage());
        }
    }
}
