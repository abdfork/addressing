<?php

namespace Tests\Form\Type;

use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Form\Type\AddressType;
use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use Symfony\Component\Form\Forms;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Form\Type\AddressType
 */
class AddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::buildForm
     * @covers ::configureOptions
     * @covers ::getName
     */
    public function testSubmitValidData()
    {
        $options['addressFormatRepository'] = new AddressFormatRepository();
        $options['countryRepository'] = new CountryRepository();
        $options['subdivisionRepository'] = new SubdivisionRepository();

        $formData = array(
            'countryCode' => 'ES',
            'administrativeArea' => 'Madrid',
            'locality' => 'Madrid',
            'givenName' => 'Test',
        );

        $address = new Address($formData['countryCode']);

        $formFactory = Forms::createFormFactory();
        $form = $formFactory->create(AddressType::class, $address, $options);

        // submit the data to the form directly
        $form->get('countryCode')->submit('ES');
        $form->get('administrativeArea')->submit('Madrid');
        $form->get('locality')->submit('Madrid');
        $form->get('givenName')->submit('Test');

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($address, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
