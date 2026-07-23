<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ارسال خودکار به شبکه‌های اجتماعی — Social auto-publish
    |--------------------------------------------------------------------------
    |
    | Both channels are disabled by default. Enable them per environment with
    | the env keys below. Sending never blocks saving a news item: every call
    | is wrapped in try/catch with a short timeout and only logs failures.
    |
    */

    // Shared HTTP timeout (seconds) for all outgoing requests.
    'timeout' => (int) env('SOCIAL_PUBLISH_TIMEOUT', 5),

    /*
    | Message template. Placeholders:
    |   {title}  — تیتر خبر
    |   {lead}   — متن کوتاه (بدون تگ HTML)
    |   {link}   — لینک کوتاه خبر
    */
    'template' => env('SOCIAL_PUBLISH_TEMPLATE', "{title}\n\n{lead}\n\n{link}"),

    'telegram' => [
        'enabled' => (bool) env('SOCIAL_PUBLISH_TELEGRAM_ENABLED', false),
        // Bot token from @BotFather, e.g. 123456:ABC-DEF...
        'bot_token' => env('SOCIAL_PUBLISH_TELEGRAM_BOT_TOKEN'),
        // Channel id or @username, e.g. @sobhesahel or -100123456789
        'channel_id' => env('SOCIAL_PUBLISH_TELEGRAM_CHANNEL_ID'),
        // Bot API base URL (override for local proxies).
        'api_url' => env('SOCIAL_PUBLISH_TELEGRAM_API_URL', 'https://api.telegram.org'),
    ],

    'whatsapp' => [
        'enabled' => (bool) env('SOCIAL_PUBLISH_WHATSAPP_ENABLED', false),
        // Generic webhook URL, or the Cloud API base (e.g.
        // https://graph.facebook.com/v19.0) when phone_id is set.
        'endpoint' => env('SOCIAL_PUBLISH_WHATSAPP_ENDPOINT'),
        // Bearer token sent with every request.
        'token' => env('SOCIAL_PUBLISH_WHATSAPP_TOKEN'),
        // WhatsApp Cloud API phone number id. When set, the request is
        // shaped as a Cloud API message to {endpoint}/{phone_id}/messages;
        // when empty, a generic JSON payload is POSTed to the endpoint.
        'phone_id' => env('SOCIAL_PUBLISH_WHATSAPP_PHONE_ID'),
        // Recipient (phone number / group id) for Cloud API messages.
        'to' => env('SOCIAL_PUBLISH_WHATSAPP_TO'),
    ],

];
