<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\Product;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductAdminForm
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

    /**
     * @var \MobileCart\CoreBundle\Service\FormHelperService
     */
    protected $formHelperService;

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $formTypeClass
     * @return $this
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param $themeConfig
     * @return $this
     */
    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeConfig
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\FormHelperService $formHelperService
     * @return $this
     */
    public function setFormHelperService(\MobileCart\CoreBundle\Service\FormHelperService $formHelperService)
    {
        $this->formHelperService = $formHelperService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\FormHelperService
     */
    public function getFormHelperService()
    {
        return $this->formHelperService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onProductAdminForm(CoreEvent $event)
    {
        /** @var |MobileCart\CoreBundle\Entity\Product $entity */
        $entity = $event->getEntity();
        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'name',
                    'sku',
                    'slug',
                    'price',
                    'is_enabled',
                    'is_public',
                    'is_taxable',
                    'is_discountable',
                ],
            ],
            'stock' => [
                'label'  => 'Stock',
                'id'     => 'stock',
                'fields' => [
                    'is_in_stock',
                    'is_qty_managed',
                    'can_backorder',
                    'qty',
                    'qty_unit',
                    'min_qty',
                    //'stock_type',
                    'upc',
                ],
            ],
            'content' => [
                'label' => 'Content',
                'id' => 'content',
                'fields' => [
                    'content',
                    'page_title',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'custom_search',
                    'sort_order',
                    'custom_template',
                ],
            ],
            'shipping' => [
                'label' => 'Shipping',
                'id' => 'shipping',
                'fields' => [
                    'is_flat_shipping',
                    'flat_shipping_price',
                    'source_address_key',
                    'weight',
                    'weight_unit',
                    'width',
                    'height',
                    'length',
                    'measure_unit',
                ],
            ],
        ];

        $customFields = $this->getFormHelperService()->addCustomFields($form, $entity);

        if ($customFields) {

            $formSections['custom'] = [
                'label' => 'Custom',
                'id' => 'custom',
                'fields' => $customFields,
            ];
        }

        $event->setReturnData('form_sections', $formSections);
        $event->setForm($form);
    }
}
