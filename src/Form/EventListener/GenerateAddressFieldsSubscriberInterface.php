<?php

namespace CommerceGuys\Addressing\Form\EventListener;

use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use Symfony\Component\Form\FormEvent;

/**
 * Interface EventSubscriber knows himself what events he is interested in.
 */
interface GenerateAddressFieldsSubscriberInterface
{
    public function buildForm($form, $countryCode, $administrativeArea, $locality);

    public function getFormFields(AddressFormat $addressFormat, $subdivisions);

    public function preSetData(FormEvent $event);

    public function preSubmit(FormEvent $event);

    public function getFieldLabels($addressFormat);
}
