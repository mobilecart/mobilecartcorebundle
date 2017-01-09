<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class ImageController extends Controller
{
    public function indexAction(Request $request)
    {
        if (!$this->getParameter('cart.image.upload.frontend.enabled')) {
            return new RedirectResponse($this->generateUrl('cart_view', []));
        }

        return $this->get('cart.theme')->render('frontend', 'Image:upload_tmp.html.twig', []);
    }

    public function uploadAction(Request $request)
    {
        if (!$this->getParameter('cart.image.upload.frontend.enabled')) {
            return new JsonResponse([
                'success' => 0,
                'message' => 'File Upload is Disabled',
            ], 400);
        }

        $objectType = $request->get('object_type', 'cart_item');
        $imageService = $this->get('cart.image');
        $uploadPath = $imageService->getImageUploadPath($objectType);
        if (!$uploadPath) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Object Type',
            ], 400);
        }

        $uploadPath = './bundles/mobilecartcore/uploads/cartitem/';
        $savePath = realpath($uploadPath);
        if (!$savePath) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Upload Path',
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

        $relPath = $uploadPath . $filename;
        $relPath = substr($relPath, 2); // remove leading "./"

        return new JsonResponse([
            'success' => 1,
            'message' => 'Upload Successful',
            'filename' => $relPath,
        ]);
    }

    public function uploadBase64Action(Request $request)
    {
        if (!$this->getParameter('cart.image.upload.frontend.enabled')) {
            return new JsonResponse([
                'success' => 0,
                'message' => 'File Upload is Disabled',
            ], 400);
        }

        $base64 = $request->get('base64', '');
        $hasError = false;

        if ($base64) {
            $parts = explode(',', $base64);
            if (isset($parts[1])) {
                $base64 = $parts[1];
            } else {
                $hasError = true;
            }
        } else {
            $hasError = true;
        }

        // block if invalid
        if ($hasError) {
            return new JsonResponse([
                'success' => 0,
                'message' => 'Invalid File Type Uploaded',
            ], 400);
        }

        $objectType = $request->get('object_type', 'cart_item');
        $imageService = $this->get('cart.image');
        $uploadPath = $imageService->getImageUploadPath($objectType);
        if (!$uploadPath) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Object Type',
            ], 400);
        }

        $savePath = realpath($uploadPath);
        if (!$savePath) {

            return new JsonResponse([
                'success' => 0,
                'message' => 'Error with Upload Path : ' . $uploadPath,
            ], 400);
        }

        $path = $this->container->getParameter('cart.upload.temp');
        $filename = time() . '_' . substr('' . microtime(), 0, 5) . '.tmp';
        $absPath = realpath($path) . '/' . $filename;

        @file_put_contents($absPath, base64_decode($base64));

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

        $relPath = $uploadPath . $filename;
        $relPath = substr($relPath, 2); // remove leading "./"

        return new JsonResponse([
            'success' => 1,
            'message' => 'Upload Successful',
            'filename' => $relPath,
        ]);
    }
}
