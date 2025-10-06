<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp;

use UnexpectedValueException;
class NormalizingLocaleProviderISO639ISO3166 implements LocaleProviderInterface
{
    /**
     * @var string
     */
    protected $locale;
    /**
     * @param string $internalLocale
     * @param string $defaultLocale
     */
    public function __construct(string $internalLocale, string $defaultLocale)
    {
        try {
            $this->locale = $this->normalizeLocale($internalLocale);
        } catch (UnexpectedValueException $exception) {
            $this->locale = $defaultLocale;
        }
    }
    /**
     * Provide system locale according to ISO 639-1 (alpha-2),
     * and according to ISO 3166-1 (alpha-2) (optionally).
     *
     * Format <language code>[_<COUNTRY CODE>].
     */
    public function provideLocale(): string
    {
        return $this->locale;
    }
    /**
     * @param string $internalLocale
     *
     * @return string
     *
     * @throws UnexpectedValueException If internal locale cannot be normalized.
     */
    protected function normalizeLocale(string $internalLocale): string
    {
        if (strlen($internalLocale) === 0) {
            throw new UnexpectedValueException('Locale cannot be an empty string');
        }
        $parts = explode('_', $internalLocale);
        if (!$this->isPartValid($parts[0])) {
            throw new UnexpectedValueException('Cannot normalize locale');
        }
        $locale = $parts['0'];
        if (!$this->isPartValid($parts[1] ?? '')) {
            return $locale;
        }
        return $locale . '_' . strtoupper($parts[1]);
    }
    /**
     * Check whether locale part (country code or language code) is valid.
     *
     * @param string $part
     *
     * @return bool
     */
    protected function isPartValid(string $part): bool
    {
        return ctype_alpha($part) && strlen($part) === 2;
    }
}
