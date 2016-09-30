<?php

namespace Tests\Form\EventListener;

use CommerceGuys\Addressing\Address;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber;
use CommerceGuys\Addressing\Form\Type\AddressType;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber
 */
class GenerateAddressFieldsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;

    /**
     * The country repository.
     *
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;

    /**
     * The form factory
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->addressFormatRepository = new AddressFormatRepository();
        $this->countryRepository = new CountryRepository();
        $this->subdivisionRepository = new SubdivisionRepository();
        $this->formFactory = Forms::createFormFactory();
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $subscriber = new GenerateAddressFieldsSubscriber($this->addressFormatRepository, $this->subdivisionRepository);
        $this->assertEquals($this->addressFormatRepository, $this->getObjectAttribute($subscriber, 'addressFormatRepository'));
        $this->assertEquals($this->subdivisionRepository, $this->getObjectAttribute($subscriber, 'subdivisionRepository'));
    }

    /**
     * @covers ::preSetData
     * @covers ::buildForm
     * @covers ::getFormFields
     * @covers ::getFieldLabels
     */
    public function testOnPreSetData()
    {
        $form = $this->formFactory->create(AddressType::class, null, array(
            'addressFormatRepository' => $this->addressFormatRepository,
            'countryRepository' => $this->countryRepository,
            'subdivisionRepository' => $this->subdivisionRepository,
        ));

        // If no address, the form should contain just the country field
        $view = $form->createView();
        $children = $view->children;
        $this->assertEquals(count($children), 1);

        // If country is set, generate the rest of the fields
        $address = new Address('ES');
        $event = new FormEvent($form, $address);
        $subscriber = new GenerateAddressFieldsSubscriber($this->addressFormatRepository, $this->subdivisionRepository);
        $subscriber->preSetData($event);
        $view = $form->createView();
        $children = $view->children;
        $this->assertEquals(count($children), 9);
    }

    /**
     * @covers ::preSubmit
     * @covers ::buildForm
     * @covers ::getFormFields
     * @covers ::getFieldLabels
     */
    public function testOnPreSubmit()
    {
        $formData = array(
            'countryCode' => 'ES'
        );

        $form = $this->formFactory->create(AddressType::class, null, array(
            'addressFormatRepository' => $this->addressFormatRepository,
            'countryRepository' => $this->countryRepository,
            'subdivisionRepository' => $this->subdivisionRepository,
        ));

        // If no address, the form should contain just the country field
        $view = $form->createView();
        $children = $view->children;
        $this->assertEquals(count($children), 1);

        // If country was entered, generate the rest of the fields
        $event = new FormEvent($form, $formData);
        $subscriber = new GenerateAddressFieldsSubscriber($this->addressFormatRepository, $this->subdivisionRepository);
        $subscriber->preSubmit($event);

        $view = $form->createView();
        $children = $view->children;
        $this->assertEquals(count($children), 9);
    }
}