<?php

namespace CommerceGuys\Addressing\Translator;

/**
 * Label translator interface.
 */
interface labelTranslatorInterface
{
    /**
     * Returns the translated value.
     *
     * @param string $key The key to translate.
     *
     * @param string $locale The locale (e.g. fr-FR).
     *
     * @return string The translated value.
     */
    public function translate($key, $locale = null);

}