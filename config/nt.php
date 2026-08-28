<?php

/*
|--------------------------------------------------------------------------
| Бизнес настройки на „Намери Техник“
|--------------------------------------------------------------------------
*/

return [
    'brand' => 'Намери Техник',
    'tagline' => 'Намери техниката за твоята задача',

    'contact' => [
        'email' => env('NT_CONTACT_EMAIL', 'info@nameritehnik.bg'),
        'phone' => env('NT_CONTACT_PHONE', '+359 700 00 000'),
    ],

    // Администраторски адрес за известия при нова заявка.
    'admin_email' => env('NT_ADMIN_EMAIL', 'admin@nameritehnik.bg'),

    /*
    | Цени на заявките (pay per lead). Стойностите тук са само резервни —
    | реалната цена се задава за всяка категория през администрацията.
    */
    'lead_price' => [
        'default' => (float) env('NT_LEAD_PRICE_DEFAULT', 15),
        'currency' => env('NT_CURRENCY', 'BGN'),
    ],

    // Абонаментни планове. Плащанията не са задължителни за MVP.
    'plans' => [
        'free' => [
            'label' => 'Безплатен',
            'monthly_lead_quota' => 5,
            'max_equipment' => 3,
            'max_images_per_equipment' => 3,
            'priority' => false,
            'featured' => false,
        ],
        'pro' => [
            'label' => 'Про',
            'monthly_lead_quota' => 30,
            'max_equipment' => 15,
            'max_images_per_equipment' => 8,
            'priority' => false,
            'featured' => false,
        ],
        'business' => [
            'label' => 'Бизнес',
            'monthly_lead_quota' => null,
            'max_equipment' => null,
            'max_images_per_equipment' => 15,
            'priority' => true,
            'featured' => true,
        ],
    ],

    // Качване на файлове.
    'uploads' => [
        'max_size_kb' => (int) env('NT_UPLOAD_MAX_KB', 5120),
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'max_width' => 2000,
        'max_lead_images' => 5,
    ],

    // Защита от спам.
    'antispam' => [
        // Ако липсват ключове, Turnstile се пропуска (напр. в локална среда).
        'turnstile_site_key' => env('TURNSTILE_SITE_KEY'),
        'turnstile_secret_key' => env('TURNSTILE_SECRET_KEY'),
        // Минимални секунди между зареждане и изпращане на формата.
        'min_form_seconds' => 4,
        // Прозорец в минути, в който еднакви заявки се смятат за дубликат.
        'duplicate_window_minutes' => (int) env('NT_DUPLICATE_WINDOW', 30),
    ],

    // Плащания. MVP работи без онлайн плащане — таксите се фактурират ръчно.
    'payments' => [
        'default' => env('NT_PAYMENT_GATEWAY', 'manual'),
        'bank' => [
            'holder' => env('NT_BANK_HOLDER'),
            'iban' => env('NT_BANK_IBAN'),
            'bic' => env('NT_BANK_BIC'),
        ],
    ],

    // Съхранение на лични данни (GDPR) — дни до анонимизиране.
    'retention_days' => (int) env('NT_RETENTION_DAYS', 730),

    'analytics' => [
        'ga_measurement_id' => env('NT_GA_MEASUREMENT_ID'),
    ],
];
