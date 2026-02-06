<?php

namespace JerryMalmstrom\PasteImage\Api\Controller;

use Flarum\Foundation\Paths;
use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Str;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UploadImageController implements RequestHandlerInterface
{
    protected SettingsRepositoryInterface $settings;
    protected Paths $paths;
    protected UrlGenerator $url;

    public function __construct(SettingsRepositoryInterface $settings, Paths $paths, UrlGenerator $url)
    {
        $this->settings = $settings;
        $this->paths = $paths;
        $this->url = $url;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $uploadedFiles = $request->getUploadedFiles();

        if (!isset($uploadedFiles['paste-image'])) {
            return new JsonResponse(['errors' => [['detail' => 'No image file provided.']]], 422);
        }

        $file = $uploadedFiles['paste-image'];

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return new JsonResponse(['errors' => [['detail' => 'File upload failed.']]], 422);
        }

        // Validate MIME type
        $allowedTypes = array_map(
            'trim',
            explode(',', $this->settings->get('jerrymalmstrom-paste-image.allowed_types', 'image/png,image/jpeg,image/gif,image/webp'))
        );
        $mimeType = $file->getClientMediaType();

        if (!in_array($mimeType, $allowedTypes, true)) {
            return new JsonResponse([
                'errors' => [['detail' => "File type '{$mimeType}' is not allowed."]],
            ], 422);
        }

        // Validate file size (setting is in KB)
        $maxSize = (int) $this->settings->get('jerrymalmstrom-paste-image.max_file_size', 2048) * 1024;

        if ($file->getSize() > $maxSize) {
            $maxKb = $maxSize / 1024;
            return new JsonResponse([
                'errors' => [['detail' => "File exceeds maximum size of {$maxKb} KB."]],
            ], 422);
        }

        // Determine file extension from MIME type
        $extensions = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = $extensions[$mimeType] ?? 'png';

        // Generate unique filename
        $filename = $actor->id . '-' . time() . '-' . Str::random(8) . '.' . $ext;

        // Ensure upload directory exists
        $uploadDir = $this->paths->public . '/assets/paste-images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Store the file
        $file->moveTo($uploadDir . '/' . $filename);

        // Build the public URL
        $url = $this->url->to('forum')->path('assets/paste-images/' . $filename);

        return new JsonResponse([
            'url' => $url,
        ]);
    }
}
