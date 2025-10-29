<?php

if (!function_exists('safe_view')) {
    /**
     * Retorna uma view com verificação automática de cache
     * 
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Illuminate\View\View
     */
    function safe_view($view, $data = [], $mergeData = [])
    {
        // Verificar se a view existe
        if (!view()->exists($view)) {
            // Tentar limpar o cache de views
            try {
                \Artisan::call('view:clear');
                \Log::warning("View '{$view}' não encontrada - cache limpo automaticamente");
                
                // Aguardar um momento para o sistema processar
                usleep(100000); // 100ms
                
                // Se ainda não existe, lançar exceção
                if (!view()->exists($view)) {
                    throw new \Exception("View '{$view}' not found even after cache clear");
                }
            } catch (\Exception $e) {
                \Log::error("Erro ao tentar recuperar view '{$view}': " . $e->getMessage());
                throw $e;
            }
        }
        
        return view($view, $data, $mergeData);
    }
}

if (!function_exists('ensure_view_cache')) {
    /**
     * Garante que o cache de views está limpo
     * 
     * @return void
     */
    function ensure_view_cache()
    {
        static $cleared = false;
        
        if (!$cleared && app()->environment('production')) {
            try {
                $viewCachePath = storage_path('framework/views');
                
                // Se não há arquivos compilados, limpar cache
                if (!file_exists($viewCachePath) || count(glob($viewCachePath . '/*.php')) === 0) {
                    \Artisan::call('view:clear');
                    \Log::info('Cache de views verificado e limpo');
                    $cleared = true;
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao verificar cache de views: ' . $e->getMessage());
            }
        }
    }
}
