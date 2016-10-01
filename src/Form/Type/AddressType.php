<?php

namespace CommerceGuys\Addressing\Form\Type;

use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatConstraint;
use CommerceGuys\Addressing\Validator\Constraints\CountryConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType.
 */
class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('countryCode', ChoiceType::class, [
            'choices' => array_flip($options['countryRepository']->getList()),
            'required' => true,
            'constraints' => array(new CountryConstraint(array('groups' => array('Default')))),

        ]);
        $builder->addEventSubscriber(new GenerateAddressFieldsSubscriber($options['addressFormatRepository'], $options['subdivisionRepository'], $options['labelTranslator']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CommerceGuys\Addressing\Address',
            'addressFormatRepository' => new AddressFormatRepository(),
            'subdivisionRepository' => new SubdivisionRepository(),
            'countryRepository' => new CountryRepository(),
            'labelTranslator' => null,
            'validation_groups' => 'Default',
            'constraints' => array(
                new AddressFormatConstraint(array('groups' => array('Default'))),
            ),
        ]);
    }
    public function getName()
    {
        return 'address';
    }
}
