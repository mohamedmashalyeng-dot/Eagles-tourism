<?php
/** Services and addresses supplied by the business owner. No inferred treatments or prices. */
function owner_services(): array
{
    return [
        [
            'category' => 'massage', 'price' => 15, 'currency' => 'EUR',
            'ar' => ['title' => 'مساج بزيت طبيعي', 'short_description' => 'جلسة مساج لمدة 45 دقيقة بزيت طبيعي بسعر 15 يورو.', 'duration' => '45 دقيقة', 'includes' => 'زيت طبيعي'],
            'en' => ['title' => 'Natural Oil Massage', 'short_description' => 'A 45-minute massage with natural oil for €15.', 'duration' => '45 minutes', 'includes' => 'Natural oil'],
            'de' => ['title' => 'Massage mit natürlichem Öl', 'short_description' => '45 Minuten Massage mit natürlichem Öl für 15 €.', 'duration' => '45 Minuten', 'includes' => 'Natürliches Öl'],
            'ru' => ['title' => 'Массаж с натуральным маслом', 'short_description' => '45 минут массажа с натуральным маслом за 15 евро.', 'duration' => '45 минут', 'includes' => 'Натуральное масло'],
            'es' => ['title' => 'Masaje con aceite natural', 'short_description' => 'Masaje de 45 minutos con aceite natural por 15 €.', 'duration' => '45 minutos', 'includes' => 'Aceite natural'],
        ],
        [
            'category' => 'beauty', 'price' => 0, 'currency' => 'EUR',
            'ar' => ['title' => 'بيوتي', 'short_description' => 'تواصل معنا لمعرفة خدمات البيوتي المتاحة والأسعار.'],
            'en' => ['title' => 'Beauty', 'short_description' => 'Contact us for available beauty services and prices.'],
            'de' => ['title' => 'Beauty', 'short_description' => 'Kontaktieren Sie uns für verfügbare Beauty-Angebote und Preise.'],
            'ru' => ['title' => 'Бьюти', 'short_description' => 'Свяжитесь с нами, чтобы узнать о доступных бьюти-услугах и ценах.'],
            'es' => ['title' => 'Belleza', 'short_description' => 'Consúltanos sobre los servicios de belleza disponibles y sus precios.'],
        ],
    ];
}

function business_locations(): array
{
    return [
        ['name' => t('سنتر إيجلز — الأهرامات الثلاثة'), 'query' => 'سنتر إيجلز الأهرامات الثلاثة الأحياء الغردقة'],
        ['name' => t('رويال بيتش'), 'query' => 'Royal Beach Al Ahyaa Hurghada Egypt'],
        ['name' => t('بالما ريزورت'), 'query' => 'Palma Resort Al Ahyaa Hurghada Egypt'],
    ];
}

/** Numeric demo prices remain private until explicitly confirmed in the dashboard. */
function has_public_price(array $trip): bool
{
    return !empty($trip['price_confirmed']) && (float)($trip['price_adult'] ?? 0) > 0;
}

function public_price(array $trip): string
{
    $currency = trim((string)($trip['price_currency'] ?? ''));
    return price($trip['price_adult'], $currency ?: null);
}
