<?php

namespace App\Helpers;

class TranslationHelper
{
    /**
     * Translation dictionary for common terms
     */
    protected static array $translations = [
        // Locations
        'West Cairo' => 'غرب القاهرة',
        'Sheikh Zayed' => 'الشيخ زايد',
        'New Capital' => 'العاصمة الإدارية الجديدة',
        'New Cairo' => 'القاهرة الجديدة',
        'Cairo' => 'القاهرة',
        'North Coast' => 'الساحل الشمالي',
        'Alexandria' => 'الإسكندرية',
        '6th of October' => 'السادس من أكتوبر',
        'October' => 'أكتوبر',
        'Heliopolis' => 'مصر الجديدة',
        'Maadi' => 'المعادي',
        'Zamalek' => 'الزمالك',
        'Nasr City' => 'مدينة نصر',
        'Giza' => 'الجيزة',
        'Mohandessin' => 'المهندسين',
        'Dokki' => 'الدقي',
        '5th Settlement' => 'التجمع الخامس',
        'Katameya' => 'القطامية',
        'Rehab City' => 'مدينة الرحاب',
        'Shorouk City' => 'مدينة الشروق',
        'Obour City' => 'مدينة العبور',

        // Project Names (Common ones)
        'Palm Hills' => 'بالم هيلز',
        'Hacienda' => 'هاسيندا',
        'Badya' => 'بادية',
        'Capital Gardens' => 'كابيتال جاردنز',
        'Club Views' => 'كلوب فيوز',

        // Status (already handled but keeping for reference)
        'In Progress' => 'قيد التنفيذ',
        'Inhabited' => 'مأهول',
        'Delivered' => 'تم التسليم',

        // Unit Types
        'Apartment' => 'شقة',
        'Villa' => 'فيلا',
        'Townhouse' => 'تاون هاوس',
        'Penthouse' => 'بنتهاوس',
        'Studio' => 'استوديو',
        'Duplex' => 'دوبلكس',
        'Chalet' => 'شاليه',
        'Twin House' => 'توين هاوس',
        'Standalone Villa' => 'فيلا مستقلة',

        // Usage Types
        'Residential' => 'سكني',
        'Commercial' => 'تجاري',
        'Administrative' => 'إداري',
        'Medical' => 'طبي',
        'Retail' => 'تجزئة',
    ];

    /**
     * Translate text from English to Arabic
     */
    public static function translate(string $text, string $locale = 'ar'): string
    {
        // If not translating to Arabic, return original
        if ($locale !== 'ar') {
            return $text;
        }

        // Check if exact match exists
        if (isset(self::$translations[$text])) {
            return self::$translations[$text];
        }

        // Check case-insensitive match
        foreach (self::$translations as $en => $ar) {
            if (strcasecmp($en, $text) === 0) {
                return $ar;
            }
        }

        // If no translation found, return original text
        return $text;
    }

    /**
     * Add a new translation to the dictionary
     */
    public static function addTranslation(string $english, string $arabic): void
    {
        self::$translations[$english] = $arabic;
    }

    /**
     * Get all translations
     */
    public static function getTranslations(): array
    {
        return self::$translations;
    }
}
