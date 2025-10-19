<?php

namespace App\Traits;

use App\Helpers\TranslationHelper;

trait HasTranslations
{
    /**
     * Get translated value for a field
     *
     * @param string $field The base field name (e.g., 'name', 'project')
     * @param string|null $locale Optional locale (en or ar), if null uses app locale
     * @return string|array
     */
    public function getTranslation(string $field, ?string $locale = null)
    {
        // If locale not specified, use app's current locale
        if ($locale === null) {
            $locale = app()->getLocale();
        }

        // If requesting specific locale, return that translation
        if (in_array($locale, ['en', 'ar'])) {
            $translatedField = "{$field}_{$locale}";
            return $this->$translatedField ?? $this->$field;
        }

        // Return both translations as object
        return [
            'en' => $this->{"{$field}_en"} ?? $this->$field,
            'ar' => $this->{"{$field}_ar"} ?? $this->$field,
        ];
    }

    /**
     * Get localized value for a field based on current app locale
     * Automatically translates using TranslationHelper if database translation is not available
     *
     * @param string $field The base field name
     * @return string
     */
    public function getLocalized(string $field): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            // First, try to get from database (field_ar)
            $dbTranslation = $this->{"{$field}_ar"};

            if ($dbTranslation) {
                return $dbTranslation;
            }

            // Second, get English value (from field_en or field)
            $englishValue = $this->{"{$field}_en"} ?? $this->$field;

            // Third, try to auto-translate using TranslationHelper
            if ($englishValue) {
                return TranslationHelper::translate($englishValue, 'ar');
            }

            return $englishValue ?? '';
        }

        return $this->{"{$field}_en"} ?? $this->$field ?? '';
    }

    /**
     * Get all translations for specified fields
     *
     * @param array $fields Array of field names to translate
     * @return array
     */
    public function getAllTranslations(array $fields): array
    {
        $translations = [];

        foreach ($fields as $field) {
            $translations[$field] = $this->getTranslation($field);
        }

        return $translations;
    }
}
