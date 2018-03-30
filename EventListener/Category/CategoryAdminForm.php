<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CategoryAdminForm
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

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
    public function onCategoryAdminForm(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Category $entity */
        $entity = $event->getEntity();

        // find variant set
        if (!$entity->getItemVarSet()) {
            $varSet = $this->getEntityService()->findOneBy(EntityConstants::ITEM_VAR_SET, [
                'object_type' => EntityConstants::CATEGORY
            ]);
            if ($varSet) {
                $entity->setItemVarSet($varSet);
            }
        }

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
                    'slug',
                    'content',
                    'parent_category',
                ],
            ],
            'content' => [
                'label' => 'Content',
                'id' => 'content',
                'fields' => [
                    'page_title',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'sort_order',
                    'custom_template',
                    'display_mode',
                ]
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
