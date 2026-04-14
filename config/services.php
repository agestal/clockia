<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mcp' => [
        'bridge_token' => env('MCP_BRIDGE_TOKEN', ''),
        'bridge_url' => env('MCP_BRIDGE_URL', 'http://clockia.local/api/mcp'),
    ],

    'llm' => [
        'provider' => env('LLM_PROVIDER', 'openrouter'),
        'model' => env('LLM_MODEL', 'openai/gpt-4o-mini'),
        'openrouter_fallback_models' => array_values(array_filter(array_map(
            static fn (string $value) => trim($value),
            explode(',', (string) env('LLM_OPENROUTER_FALLBACK_MODELS', 'openai/gpt-4o-mini,openai/gpt-4.1-nano'))
        ))),
        'temperature' => (float) env('LLM_TEMPERATURE', 0.15),
        'max_tokens' => (int) env('LLM_MAX_TOKENS', 700),
        'timeout' => (int) env('LLM_TIMEOUT', 20),
        'conversation_ttl_minutes' => (int) env('LLM_CONVERSATION_TTL_MINUTES', 120),
        'conversation_max_turns' => (int) env('LLM_CONVERSATION_MAX_TURNS', 12),
        'conversation_max_history' => (int) env('LLM_CONVERSATION_MAX_HISTORY', 40),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', ''),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY', ''),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

];
