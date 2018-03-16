<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentAdminForm
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentAdminForm
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
    public function onContentAdminForm(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Content $entity */
        $entity = $event->getEntity();

        // find variant set
        if (!$entity->getId() && !$entity->getItemVarSet()) {
            $varSet = $this->getEntityService()->findOneBy(EntityConstants::ITEM_VAR_SET, [
                'object_type' => EntityConstants::CONTENT
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
                    'name', // needed ?
                    'slug',
                    'author',
                    'sort_order',
                    'is_public',
                    'is_searchable',
                ],
            ],
            'content' => [
                'label' => 'Content',
                'id' => 'content',
                'fields' => [
                    'content',
                    'page_title',
                    //'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'custom_template',
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

        $event->setReturnData('content_types', EntityConstants::getContentTypes());
        $event->setReturnData('form_sections', $formSections);
        $event->setForm($form);
    }
}
