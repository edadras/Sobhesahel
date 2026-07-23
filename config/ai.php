<?php

return [

    /*
    |--------------------------------------------------------------------------
    | هوش مصنوعی — AI assistant (OpenAI-compatible)
    |--------------------------------------------------------------------------
    |
    | Disabled by default: with AI_ENABLED=false (or missing credentials) every
    | AI feature in the admin panel is hidden and AiService becomes a no-op,
    | so the site works exactly as before. Point AI_BASE_URL at any
    | OpenAI-compatible endpoint (OpenAI, OpenRouter, Groq, a local proxy,
    | an Iranian gateway, ...). Calls never block saving content: they are
    | wrapped in try/catch with a bounded timeout and only log failures.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', false),

    // Base URL of the OpenAI-compatible API, e.g. https://api.openai.com/v1
    'base_url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),

    'api_key' => env('AI_API_KEY'),

    // Chat model name, e.g. gpt-4o-mini or any model your endpoint serves.
    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    // HTTP timeout (seconds) for a single completion request.
    'timeout' => (int) env('AI_TIMEOUT', 20),
];
