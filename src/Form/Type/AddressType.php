<?php

namespace CommerceGuys\Addressing\Form\Type;

use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Form\PropertyAccess\AddressPropertyAccessor;
use CommerceGuys\Addressing\Form\EventListener\GenerateAddressFieldsSubscriber;
use CommerceGuys\Addressing\Validator\Constraints\CountryConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
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
        $builder
            ->add('countryCode', ChoiceType::class, [
                'choices' => array_flip($options['countryRepository']->getList()),
                'required' => true,
                'constraints' => array(new CountryConstraint(array('groups' => array('Default')))),
            ])
            ->addEventSubscriber(new GenerateAddressFieldsSubscriber($options['addressFormatRepository'], $options['subdivisionRepository'], $options['labelTranslator']))
            ->setDataMapper(new PropertyPathMapper(new AddressPropertyAccessor()))
        ;
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
            'csrf_protection' => false,
        ]);
    }
    public function getName()
    {
        return 'address';
    }
}
