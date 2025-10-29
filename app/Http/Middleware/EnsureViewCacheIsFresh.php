<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class EnsureViewCacheIsFresh
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o cache de views existe
        $viewCachePath = storage_path('framework/views');
        
        // Se o cache estiver vazio ou corrompido, limpar
        if ($this->shouldClearCache($viewCachePath)) {
            try {
                Artisan::call('view:clear');
                \Log::info('Cache de views limpo automaticamente pelo middleware');
            } catch (\Exception $e) {
                \Log::error('Erro ao limpar cache de views: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
    
    /**
     * Verificar se deve limpar o cache
     */
    private function shouldClearCache($path): bool
    {
        // Se a pasta não existe, não precisa limpar
        if (!File::exists($path)) {
            return false;
        }
        
        // Se a pasta está vazia (exceto .gitignore), não precisa limpar
        $files = File::files($path);
        
        // Filtrar apenas arquivos .php (arquivos de cache compilado)
        $cacheFiles = array_filter($files, function($file) {
            return $file->getExtension() === 'php';
        });
        
        // Se não há arquivos de cache, não precisa limpar
        return count($cacheFiles) === 0;
    }
}
