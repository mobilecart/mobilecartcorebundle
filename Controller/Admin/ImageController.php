<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use MobileCart\CoreBundle\Entity\Item;
use MobileCart\CoreBundle\Entity\ItemImage;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Image controller.
 *
 * @Route("/admin/image")
 */
class ImageController extends Controller
{
    /**
     * @Route("/", name="cart_admin_image")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $imageUrl = $request->get('url', '');
        if (empty($imageUrl)) {
            header('HTTP/1.1 404 Not Found');
            header("Content-Type: text/plain");
            die();
        }
        try {
            $imageType = @exif_imagetype($imageUrl);
            if ($imageType != IMAGETYPE_GIF && $imageType != IMAGETYPE_JPEG && $imageType != IMAGETYPE_PNG) {
                throw new \Exception('The URL does not contain an image');
            }
            $mimeTypes = array();
            $mimeTypes[IMAGETYPE_GIF] = 'image/gif';
            $mimeTypes[IMAGETYPE_JPEG] = 'image/jpeg';
            $mimeTypes[IMAGETYPE_PNG] = 'image/png';
            header('Content-Type: ' . $mimeTypes[$imageType]);
            define('ONE_YEAR_SECONDS', 31536000);
            header('Pragma: public');
            header('Cache-Control: public,maxage=' . ONE_YEAR_SECONDS);
            header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
            header('Expires: ' . gmdate('D, d M Y H:i:s', (time() + ONE_YEAR_SECONDS)) . ' GMT');
            header_remove('Set-Cookie');
            readfile($imageUrl);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $cause = $e->getPrevious() ? $e->getPrevious()->getMessage() : '';
            header("HTTP/1.1 501 Internal Server Error");
            header("Content-Type: text/plain");
            echo $message;
            echo $cause;
            die();
        }
        die();
    }

    /**
     * @Route("/upload", name="cart_admin_image_upload")
     * @Method("PUT")
     */
    public function uploadAction(Request $request)
    {
        $itemId = $request->get('item_id', '');

        $objectType = $request->get('object_type', '');
        if (!$objectType) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Please Select Object Type',
            ], 400);
        }

        $imageCode = $request->get('image_code', '');
        if (!$imageCode) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Please Select Image Size',
            ], 400);
        }

        // todo : allow filename control
        // $requestedFilename = $request->get('filename', '');

        $entityService = $this->get('cart.entity');
        $imageService = $this->get('cart.image');

        $dimensions = $imageService->getImageConfig($objectType, $imageCode);
        if (!$dimensions || !isset($dimensions['width']) || !isset($dimensions['height'])) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Image Size Configuration : width, height',
            ], 400);
        }

        $uploadPath = $imageService->getImageUploadPath($objectType);
        $savePath = realpath($uploadPath);
        if (!$savePath) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Upload Path : ' . $uploadPath,
            ], 400);
        }

        // $rootPath = $this->get('kernel')->getRootDir();
        $path = $this->container->getParameter('cart.upload.temp');
        $filename = time() . '_' . substr('' . microtime(), 0, 5) . '.tmp';
        $absPath = realpath($path) . '/' . $filename;

        $write = fopen($absPath, 'w');
        $read = fopen('php://input', 'r');

        $size = 0;
        while (!feof($read)) {
            $buffer = fread($read, 1028);
            fwrite($write, $buffer);
            $size += strlen($buffer);
        }
        fclose($write);

        $mimeType = mime_content_type($absPath);
        $mimeTypeParts = explode('/', $mimeType);
        if ($mimeTypeParts[0] != 'image') {

            // delete file
            unlink($absPath);

            return new JsonResponse([
                'success' => 0,
                'message' => 'Invalid File Type Uploaded',
            ], 400);
        }

        $ext = $mimeTypeParts[1] == 'jpeg'
            ? 'jpg'
            : $mimeTypeParts[1]; // png, gif

        // move file
        $filename = str_replace('.tmp', '.' . $ext, $filename);

        $newPath = $savePath . '/' . $filename;
        rename($absPath, $newPath);

        // todo : observer

        $thumb = new \Imagick($newPath);
        $thumb->resizeImage($dimensions['width'], $dimensions['height'], \Imagick::FILTER_LANCZOS, 1);
        $thumb->writeImage($newPath);
        $thumb->destroy();

        $relPath = $uploadPath . $filename;
        $relPath = substr($relPath, 2); // remove leading "./"

        $imageObjectType = $objectType . '_image'; // naming scheme
        $itemImage = $entityService->getInstance($imageObjectType);
        $itemImage
            ->setCode($imageCode)
            ->setSize($size)
            ->setWidth($dimensions['width'])
            ->setHeight($dimensions['height'])
            ->setPath($relPath)
            ->setSortOrder(1)
            ->setAltText('');

        if ($itemId) {
            $item = $entityService->find($objectType, $itemId);
            if ($item) {
                $itemImage->setParent($item);
            }
        }

        $entityService->persist($itemImage);

        $id = $itemImage->getId();

        return new JsonResponse([
            'success'       => 1,
            'message'       => 'Image was Uploaded',
            'id'            => $id,
            'item_id'       => $itemId,
            'width'         => $dimensions['width'],
            'height'        => $dimensions['height'],
            'code'          => $imageCode,
            'bytes'         => $size,
            'relative_path' => $relPath,
        ]);
    }

    /**
     * @Route("/upload_slot", name="cart_admin_image_upload_slot")
     * @Method("PUT")
     */
    public function uploadSlotAction(Request $request)
    {
        $itemId = $request->get('item_id', '');

        $objectType = EntityConstants::CONTENT;

        $entityService = $this->get('cart.entity');
        $imageService = $this->get('cart.image');

        $uploadPath = $imageService->getImageUploadPath($objectType);
        $savePath = realpath($uploadPath);
        if (!$savePath) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Upload Path : ' . $uploadPath,
            ], 400);
        }

        // $rootPath = $this->get('kernel')->getRootDir();
        $path = $this->container->getParameter('cart.upload.temp');
        $filename = time() . '_' . substr('' . microtime(), 0, 5) . '.tmp';
        $absPath = realpath($path) . '/' . $filename;

        $write = fopen($absPath, 'w');
        $read = fopen('php://input', 'r');

        $size = 0;
        while (!feof($read)) {
            $buffer = fread($read, 1028);
            fwrite($write, $buffer);
            $size += strlen($buffer);
        }
        fclose($write);

        $mimeType = mime_content_type($absPath);
        $mimeTypeParts = explode('/', $mimeType);
        if ($mimeTypeParts[0] != 'image') {

            // delete file
            unlink($absPath);

            return new JsonResponse([
                'success' => 0,
                'message' => 'Invalid File Type Uploaded',
            ], 400);
        }

        $ext = $mimeTypeParts[1] == 'jpeg'
            ? 'jpg'
            : $mimeTypeParts[1]; // png, gif

        // move file
        $filename = str_replace('.tmp', '.' . $ext, $filename);

        $newPath = $savePath . '/' . $filename;
        rename($absPath, $newPath);

        // todo : observer

        $relPath = $uploadPath . $filename;
        $relPath = substr($relPath, 2); // remove leading "./"

        $imageObjectType = EntityConstants::CONTENT_SLOT; // naming scheme
        $contentSlot = $entityService->getInstance($imageObjectType);
        $contentSlot
            ->setContentType(EntityConstants::CONTENT_TYPE_IMAGE)
            ->setPath($relPath)
            ->setSortOrder(1)
            ->setAltText('');

        if ($itemId) {
            $item = $entityService->find($objectType, $itemId);
            if ($item) {
                $contentSlot->setParent($item);
            }
        }

        $entityService->persist($contentSlot);

        return new JsonResponse(array_merge(
            [
                'success'       => 1,
                'message'       => 'Image was Uploaded',
            ],
            $contentSlot->getBaseData()
        ));
    }
}
