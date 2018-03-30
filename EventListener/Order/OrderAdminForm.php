<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class OrderAdminForm
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \MobileCart\CoreBundle\Service\FormHelperService
     */
    protected $formHelperService;

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
    }

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
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
    public function onOrderAdminForm(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Order $entity */
        $entity = $event->getEntity();

        // find variant set
        if (!$entity->getItemVarSet()) {
            $varSet = $this->getEntityService()->findOneBy(EntityConstants::ITEM_VAR_SET, [
                'object_type' => EntityConstants::ORDER
            ]);
            if ($varSet) {
                $entity->setItemVarSet($varSet);
            }
        }

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $customFields = $this->getFormHelperService()->addCustomFields($form, $entity);

        if ($customFields) {

            $formSections['custom'] = [
                'label' => 'Custom',
                'id' => 'custom',
                'fields' => $customFields,
            ];
        }

        $event->setReturnData('form_sections', $formSections);
        $event->setReturnData('country_regions', $this->getCartService()->getCountryRegions());
        $event->setForm($form);
    }
}
