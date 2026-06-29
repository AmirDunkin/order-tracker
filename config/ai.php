<?php

declare(strict_types=1);

return [
    'openai_api_key' => getenv('OPENAI_API_KEY') ?: '',
    'openai_model'   => getenv('OPENAI_MODEL') ?: 'gpt-4o-mini',
    'openai_url'     => 'https://api.openai.com/v1/chat/completions',
];
