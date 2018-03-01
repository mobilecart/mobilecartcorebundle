<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentUpdate
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

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
     * @param CoreEvent $event
     */
    public function onContentUpdate(CoreEvent $event)
    {
        $request = $event->getRequest();

        /** @var \MobileCart\CoreBundle\Entity\Content $entity */
        $entity = $event->getEntity();

        try {
            $this->getEntityService()->persist($entity);
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $event->setSuccess(false);
            $event->addErrorMessage('An error occurred while saving Content');
            return;
        }

        if ($event->getFormData()) {
            try {
                $this->getEntityService()->persistVariants($entity, $event->getFormData());
            } catch(\Exception $e) {
                $this->getEntityService()->rollBack();
                $event->setSuccess(false);
                $event->addErrorMessage('An error occurred while saving Content');
                return;
            }
        }

        // update slots
        if ($slots = $request->get('slots', [])) {

            $sortOrder = 1;
            foreach($slots as $k => $slot) {
                $slots[$k]['sort_order'] = $sortOrder;
                $sortOrder++;
            }

            try {
                $this->updateContentSlots($entity, $slots);
            } catch(\Exception $e) {
                $event->addErrorMessage('An error occurred while saving Content Slots');
            }
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {

                foreach($images as $k => $image) {

                    if (!isset($image->sort_order)) {
                        $image->sort_order = 1;
                        $images[$k] = $image;
                    }

                    if (!$image->sort_order) {
                        $image->sort_order = 1;
                        $images[$k] = $image;
                    }
                }

                try {
                    $this->getEntityService()->updateImages(EntityConstants::CONTENT_IMAGE, $entity, $images);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving a Content Image');
                }
            }
        }

        $this->getEntityService()->commit();
        $event->setSuccess(true);
        $event->addSuccessMessage('Content Updated !');
    }

    /**
     * Update Content Slots within a Content Entity
     *
     * @param $entity
     * @param array $slots
     * @return $this
     */
    public function updateContentSlots($entity, array $slots)
    {
        $objectType = EntityConstants::CONTENT_SLOT;
        if (is_int($entity)) {
            $entity = $this->getEntityService()->find($objectType, $entity);
        }

        // get slots
        $currentSlots = $entity->getSlots();
        if ($currentSlots) {
            foreach($currentSlots as $contentSlot) {
                $found = false;
                foreach($slots as $idx => $data) {

                    if ($data['id'] != $contentSlot->getId()) {
                        continue;
                    }

                    $embedCode = isset($data['embed_code'])
                        ? $data['embed_code']
                        : '';

                    $title = isset($data['title'])
                        ? $data['title']
                        : '';

                    $bodyText = isset($data['body_text'])
                        ? $data['body_text']
                        : '';

                    $sortOrder = isset($data['sort_order'])
                        ? $data['sort_order']
                        : 1;

                    switch($data['content_type']) {
                        case EntityConstants::CONTENT_TYPE_IMAGE:

                            // update slot
                            $contentSlot
                                ->setParent($entity)
                                ->setContentType(EntityConstants::CONTENT_TYPE_IMAGE)
                                ->setTitle($title)
                                ->setBodyText($bodyText)
                                ->setSortOrder($sortOrder)
                                ->setEmbedCode('');

                            if (isset($data['url'])) {
                                $contentSlot->setUrl($data['url']);
                            }

                            if (isset($data['path'])) {
                                $contentSlot->setPath($data['path']);
                            }

                            if (isset($data['alt_text'])) {
                                $contentSlot->setAltText($data['alt_text']);
                            }

                            break;
                        case EntityConstants::CONTENT_TYPE_EMBED:

                            // update slot
                            $contentSlot
                                ->setParent($entity)
                                ->setContentType(EntityConstants::CONTENT_TYPE_EMBED)
                                ->setTitle($title)
                                ->setBodyText($bodyText)
                                ->setSortOrder($sortOrder)
                                ->setAltText('')
                                ->setUrl('')
                                ->setEmbedCode($embedCode)
                                ->setPath('');

                            break;
                        case EntityConstants::CONTENT_TYPE_HTML:

                            // update slot
                            $contentSlot
                                ->setParent($entity)
                                ->setContentType(EntityConstants::CONTENT_TYPE_HTML)
                                ->setTitle($title)
                                ->setBodyText($bodyText)
                                ->setSortOrder($sortOrder)
                                ->setAltText('')
                                ->setUrl('')
                                ->setEmbedCode('')
                                ->setPath('');

                            break;
                        default:

                            break;
                    }

                    $this->getEntityService()->persist($contentSlot);

                    unset($slots[$idx]);
                    $found = true;
                    break;
                }

                // remove the slot if it's not included
                if (!$found) {
                    $this->getEntityService()->remove($contentSlot);
                }
            }
        }

        if ($slots) {
            foreach($slots as $data) {

                $contentSlot = $this->getEntityService()->find($objectType, $data['id']);

                $embedCode = isset($data['embed_code'])
                    ? $data['embed_code']
                    : '';

                $title = isset($data['title'])
                    ? $data['title']
                    : '';

                $bodyText = isset($data['body_text'])
                    ? $data['body_text']
                    : '';

                $sortOrder = isset($data['sort_order'])
                    ? $data['sort_order']
                    : 1;

                switch($data['content_type']) {
                    case EntityConstants::CONTENT_TYPE_IMAGE:

                        // update slot
                        $contentSlot
                            ->setParent($entity)
                            ->setContentType(EntityConstants::CONTENT_TYPE_IMAGE)
                            ->setTitle($title)
                            ->setBodyText($bodyText)
                            ->setSortOrder($sortOrder)
                            ->setEmbedCode('');

                        if (isset($data['url'])) {
                            $contentSlot->setUrl($data['url']);
                        }

                        if (isset($data['path'])) {
                            $contentSlot->setPath($data['path']);
                        }

                        if (isset($data['alt_text'])) {
                            $contentSlot->setAltText($data['alt_text']);
                        }

                        break;
                    case EntityConstants::CONTENT_TYPE_EMBED:

                        // update slot
                        $contentSlot
                            ->setParent($entity)
                            ->setContentType(EntityConstants::CONTENT_TYPE_EMBED)
                            ->setTitle($title)
                            ->setBodyText($bodyText)
                            ->setSortOrder($sortOrder)
                            ->setAltText('')
                            ->setUrl('')
                            ->setEmbedCode($embedCode)
                            ->setPath('')
                        ;

                        break;
                    case EntityConstants::CONTENT_TYPE_HTML:

                        // update slot
                        $contentSlot
                            ->setParent($entity)
                            ->setContentType(EntityConstants::CONTENT_TYPE_HTML)
                            ->setTitle($title)
                            ->setBodyText($bodyText)
                            ->setSortOrder($sortOrder)
                            ->setAltText('')
                            ->setUrl('')
                            ->setEmbedCode('')
                            ->setPath('');

                        break;
                    default:

                        break;
                }

                $this->getEntityService()->persist($contentSlot);
            }
        }

        return $this;
    }
}
