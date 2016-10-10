<?php

namespace CommerceGuys\Addressing\Form\EventListener;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use CommerceGuys\Addressing\AddressFormat\DependentLocalityType;
use CommerceGuys\Addressing\AddressFormat\LocalityType;
use CommerceGuys\Addressing\AddressFormat\PostalCodeType;
use CommerceGuys\Addressing\AddressFormat\AddressFormatHelper;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use CommerceGuys\Addressing\Translator\labelTranslator;
use CommerceGuys\Addressing\Translator\labelTranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class GenerateAddressFieldsSubscriber.
 */
class GenerateAddressFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * The address format repository.
     *
     * @var AddressFormatRepositoryInterface
     */
    protected $addressFormatRepository;
    /**
     * The subdivision repository.
     *
     * @var SubdivisionRepositoryInterface
     */
    protected $subdivisionRepository;
    /**
     * Creates a GenerateAddressFieldsSubscriber instance.
     *
     * @param AddressFormatRepositoryInterface $addressFormatRepository
     * @param SubdivisionRepositoryInterface   $subdivisionRepository
     */
    public function __construct(AddressFormatRepositoryInterface $addressFormatRepository, SubdivisionRepositoryInterface $subdivisionRepository)
    {
        $this->addressFormatRepository = $addressFormatRepository;
        $this->subdivisionRepository = $subdivisionRepository;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $address = $event->getData();

        $form = $event->getForm();
        if (null === $address) {
            return;
        }
        $countryCode = $address->getCountryCode();
        $administrativeArea = $address->getAdministrativeArea();
        $locality = $address->getLocality();
        $this->buildForm($form, $countryCode, $administrativeArea, $locality);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $countryCode = array_key_exists('countryCode', $data) ? $data['countryCode'] : null;
        $administrativeArea = array_key_exists('administrativeArea', $data) ? $data['administrativeArea'] : null;
        $locality = array_key_exists('locality', $data) ? $data['locality'] : null;
        $this->buildForm($form, $countryCode, $administrativeArea, $locality);
    }

    /**
     * Builds the address form for the provided country code.
     *
     * @param FormInterface            $form
     * @param string                   $countryCode        The country code
     * @param string                   $administrativeArea The administrative area
     * @param string                   $locality           The locality
     * @param labelTranslatorInterface $translator
     */
    protected function buildForm(FormInterface $form, $countryCode, $administrativeArea, $locality, labelTranslatorInterface $translator = null)
    {
        $addressFormat = $this->addressFormatRepository->get($countryCode);

        if (empty($translator)) {
            $translator = new labelTranslator($addressFormat->getLocale());
        }

        // A list of needed subdivisions and their parent ids.
        $subdivisions = [
            AddressField::ADMINISTRATIVE_AREA => 0,
        ];
        if (!empty($administrativeArea)) {
            $subdivisions[AddressField::LOCALITY] = $administrativeArea;
        }
        if (!empty($locality)) {
            $subdivisions[AddressField::DEPENDENT_LOCALITY] = $locality;
        }
        $fields = $this->getFormFields($addressFormat, $subdivisions, $translator);
        foreach ($fields as $field => $fieldOptions) {
            $type = isset($fieldOptions['choices']) ? ChoiceType::class : TextType::class;
            $form->add($field, $type, $fieldOptions);
        }
    }

    /**
     * Gets a list of form fields for the provided address format.
     *
     * @param AddressFormat            $addressFormat
     * @param array                    $subdivisions  An array of needed subdivisions
     * @param labelTranslatorInterface $translator
     *
     * @return array An array in the $field => $formOptions format
     */
    protected function getFormFields(AddressFormat $addressFormat, $subdivisions, labelTranslatorInterface $translator)
    {
        $fields = [];
        $labels = $this->getFieldLabels($addressFormat, $translator);
        $requiredFields = $addressFormat->getRequiredFields();
        $format = $addressFormat->getFormat();
        $groupedFields = AddressFormatHelper::getGroupedFields($format);
        foreach ($groupedFields as $lineFields) {
            foreach ($lineFields as $field) {
                $fields[$field] = [
                    'label' => $labels[$field],
                    'required' => in_array($field, $requiredFields),
                ];
            }
        }
        // Add choices for predefined subdivisions.
        foreach ($subdivisions as $field => $parentId) {
            // @todo Pass the form locale to get the translated values.
            $children = $this->subdivisionRepository->getList(array($addressFormat->getCountryCode()));
            if ($children) {
                $fields[$field]['choices'] = $children;
            }
        }

        return $fields;
    }

    /**
     * Gets the labels for the provided address format's fields.
     *
     * @param AddressFormat            $addressFormat
     * @param labelTranslatorInterface $translator
     *
     * @return array An array of labels keyed by field constants
     */
    protected function getFieldLabels($addressFormat, labelTranslatorInterface $translator)
    {
        // All possible subdivision labels.
        $subdivisionLabels = [
            AdministrativeAreaType::AREA => $translator->translate(AdministrativeAreaType::AREA),
            AdministrativeAreaType::COUNTY => $translator->translate(AdministrativeAreaType::COUNTY),
            AdministrativeAreaType::DEPARTMENT => $translator->translate(AdministrativeAreaType::DEPARTMENT),
            AdministrativeAreaType::DISTRICT => $translator->translate(AdministrativeAreaType::DISTRICT),
            AdministrativeAreaType::DO_SI => $translator->translate(AdministrativeAreaType::DO_SI),
            AdministrativeAreaType::EMIRATE => $translator->translate(AdministrativeAreaType::EMIRATE),
            AdministrativeAreaType::ISLAND => $translator->translate(AdministrativeAreaType::ISLAND),
            AdministrativeAreaType::OBLAST => $translator->translate(AdministrativeAreaType::OBLAST),
            AdministrativeAreaType::PARISH => $translator->translate(AdministrativeAreaType::PARISH),
            AdministrativeAreaType::PREFECTURE => $translator->translate(AdministrativeAreaType::PREFECTURE),
            AdministrativeAreaType::PROVINCE => $translator->translate(AdministrativeAreaType::PROVINCE),
            AdministrativeAreaType::STATE => $translator->translate(AdministrativeAreaType::STATE),
            LocalityType::CITY => $translator->translate(LocalityType::CITY),
            LocalityType::DISTRICT => $translator->translate(LocalityType::DISTRICT),
            LocalityType::POST_TOWN => $translator->translate(LocalityType::POST_TOWN),
            DependentLocalityType::DISTRICT => $translator->translate(DependentLocalityType::DISTRICT),
            DependentLocalityType::NEIGHBORHOOD => $translator->translate(DependentLocalityType::NEIGHBORHOOD),
            DependentLocalityType::VILLAGE_TOWNSHIP => $translator->translate(DependentLocalityType::VILLAGE_TOWNSHIP),
            DependentLocalityType::SUBURB => $translator->translate(DependentLocalityType::SUBURB),
            PostalCodeType::POSTAL => $translator->translate(PostalCodeType::POSTAL),
            PostalCodeType::ZIP => $translator->translate(PostalCodeType::ZIP),
            PostalCodeType::PIN => $translator->translate(PostalCodeType::PIN),
        ];

        // Determine the correct administrative area label.
        $administrativeAreaType = $addressFormat->getAdministrativeAreaType();
        $administrativeAreaLabel = '';
        if (isset($subdivisionLabels[$administrativeAreaType])) {
            $administrativeAreaLabel = $subdivisionLabels[$administrativeAreaType];
        }
        // Determine the correct locality label.
        $localityType = $addressFormat->getLocalityType();
        $localityLabel = '';
        if (isset($subdivisionLabels[$localityType])) {
            $localityLabel = $subdivisionLabels[$localityType];
        }
        // Determine the correct dependent locality label.
        $dependentLocalityType = $addressFormat->getDependentLocalityType();
        $dependentLocalityLabel = '';
        if (isset($subdivisionLabels[$dependentLocalityType])) {
            $dependentLocalityLabel = $subdivisionLabels[$dependentLocalityType];
        }
        // Determine the correct postal code label.
        $postalCodeType = $addressFormat->getPostalCodeType();
        $postalCodeLabel = $subdivisionLabels[PostalCodeType::POSTAL];
        if (isset($subdivisionLabels[$postalCodeType])) {
            $postalCodeLabel = $subdivisionLabels[$postalCodeType];
        }
        // Assemble the final set of labels.
        $labels = [
            AddressField::ADMINISTRATIVE_AREA => $translator->translate($administrativeAreaLabel),
            AddressField::LOCALITY => $translator->translate($localityLabel),
            AddressField::DEPENDENT_LOCALITY => $translator->translate($dependentLocalityLabel),
            AddressField::ADDRESS_LINE1 => $translator->translate(AddressField::ADDRESS_LINE1),
            AddressField::ADDRESS_LINE2 => $translator->translate(AddressField::ADDRESS_LINE2),
            AddressField::ORGANIZATION => $translator->translate(AddressField::ORGANIZATION),
            AddressField::GIVEN_NAME => $translator->translate(AddressField::GIVEN_NAME),
            AddressField::ADDITIONAL_NAME => $translator->translate(AddressField::ADDITIONAL_NAME),
            AddressField::FAMILY_NAME => $translator->translate(AddressField::FAMILY_NAME),
            // Google's libaddressinput provides no label for this field type,
            // Google wallet calls it "CEDEX" for every country that uses it.
            AddressField::SORTING_CODE => $translator->translate(AddressField::SORTING_CODE),
            AddressField::POSTAL_CODE => $translator->translate($postalCodeLabel),
        ];

        return $labels;
    }
}
