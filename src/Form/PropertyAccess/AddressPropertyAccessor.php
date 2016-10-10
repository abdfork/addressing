<?php

namespace CommerceGuys\Addressing\Form\PropertyAccess;

use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AddressPropertyAccessor extends PropertyAccessor
{
    /**
     * {@inheritdoc}
     */
    public function setValue(&$address, $propertyPath, $value)
    {
        $camelized = $this->camelize($propertyPath);
        $function = 'with'.$camelized;

        if (method_exists($address, $function)) {
            $newAddress = $address->{$function}($value);
            $address = $newAddress;
        } else {
            throw new InvalidArgumentException('Neither the property '.$propertyPath.' nor one the methods '.$function.
                ' exist and have public access in class '.get_class($address));
        }
    }

    /**
     * Camelizes a given string.
     *
     * @param string $string Some string
     *
     * @return string The camelized version of the string
     */
    private function camelize($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
