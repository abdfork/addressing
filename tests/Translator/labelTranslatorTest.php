<?php

namespace Tests\Translator;

use CommerceGuys\Addressing\Translator\labelTranslator;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Translator\labelTranslator
 */
class labelTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var labelTranslator $translator */
    protected $translator;

    /** @var string $locale */
    protected $locale;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->locale = 'es_ES';
        $this->translator = new labelTranslator($this->locale);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        if (class_exists('\Symfony\Component\Translation\Translator')) {
            $this->assertInstanceOf('\Symfony\Component\Translation\Translator', $this->getObjectAttribute($this->translator, 'translator'));
        }
    }

    /**
     * @covers ::translate
     */
    public function testTranslate()
    {
        if (class_exists('\Symfony\Component\Translation\Translator')) {
            $this->assertEquals($this->translator->translate('city'), 'Ciudad');
        } else {
            $this->assertEquals($this->translator->translate('city'), 'city');
        }
    }
}
