<?php


namespace CommerceGuys\Addressing\Translator;


class labelTranslator implements labelTranslatorInterface
{
    /** @var Translator $translator */
    protected $translator;

    /**
     * labelTranslator constructor.
     *
     * @param $locale
     */
    public function __construct($locale = null)
    {
        if(class_exists('\Symfony\Component\Translation\Translator')) {
            $this->translator = new \Symfony\Component\Translation\Translator($locale);
            $this->loadTranslations();
        }
    }

    /**
     * loads Translations file
     */
    protected function loadTranslations()
    {
        $this->translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
        $this->translator->addResource('yaml', __DIR__ . '/../../resources/translations/addressLabels.en_US.yml', 'en_US', 'addressing');
        $this->translator->addResource('yaml', __DIR__ . '/../../resources/translations/addressLabels.es_ES.yml', 'es_ES', 'addressing');

        $this->translator->setFallbackLocales(array('en_US'));
    }

    /**
     * {@inheritdoc}
     */
    public function translate($key, $locale = null)
    {
        if(empty($this->translator)) {
            return $key;
        }

        return $this->translator->trans($key, array(), 'addressing', $locale);
    }
}