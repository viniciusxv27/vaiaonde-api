<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AbacatePay API Key
    |--------------------------------------------------------------------------
    |
    | Chave de API do AbacatePay obtida no painel administrativo.
    | https://abacatepay.com/dashboard
    |
    */
    'api_key' => env('ABACATEPAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret (opcional)
    |--------------------------------------------------------------------------
    |
    | Segredo usado para validar a assinatura dos webhooks do AbacatePay.
    | Aumenta a segurança verificando que as notificações são autênticas.
    |
    */
    'webhook_secret' => env('ABACATEPAY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | URL do webhook configurada no AbacatePay.
    | Exemplo: https://seudominio.com/api/webhook/abacatepay
    |
    */
    'webhook_url' => env('APP_URL') . '/api/webhook/abacatepay',
];
