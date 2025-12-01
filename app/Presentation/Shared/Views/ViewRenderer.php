<?php
declare(strict_types=1);

namespace App\Presentation\Shared\Views;

use Nyholm\Psr7\Response; // Lightweight PSR-7 implementation
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class ViewRenderer implements ViewRendererInterface
{
    private array $globalData = [];
    private ?string $layoutTemplate = null;

    public function __construct(
        private readonly string $baseTemplatePath,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function setLayout(string $layoutTemplate): void
    {
        $this->layoutTemplate = $layoutTemplate;
    }

    public function setGlobalData(array $data): void
    {
        $this->globalData = $data;
    }

    public function render(string $templatePath, array $data = []): string
    {
        $fullPath = $this->resolveTemplatePath($templatePath);

        if (!is_file($fullPath) || !is_readable($fullPath)) {
            $this->logger->error("Template not found or not readable", ['template' => $fullPath]);
            throw new RuntimeException("Template not found or not readable: {$fullPath}");
        }

        $data = array_merge($this->globalData, $data);
        extract($this->sanitizeData($data), EXTR_SKIP);

        ob_start();
        try {
            include $fullPath;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            $this->logger->error("Error rendering template", ['exception' => $e]);
            throw $e;
        }
    }

    public function renderContent(array $data = [], array $partials = []): ResponseInterface
    {
        foreach ($partials as $key => $partialTemplate) {
            $data[$key] = $this->render($partialTemplate);
        }

        $contentHtml = $this->render('content.php', $data);

        if ($this->layoutTemplate !== null) {
            $fullPageHtml = $this->render($this->layoutTemplate, array_merge($data, ['content' => $contentHtml]));
        } else {
            $fullPageHtml = $contentHtml;
        }

        return $this->createHtmlResponse($fullPageHtml);
    }

    public function renderPartial(string $templatePath, array $data = []): ResponseInterface
    {
        $html = $this->render($templatePath, $data);
        return $this->createHtmlResponse($html);
    }

    public function renderLoading(): ResponseInterface
    {
        $html = $this->render('loading.php');
        return $this->createHtmlResponse($html);
    }

    public function renderErrorTemplate(array $errorDetails): ResponseInterface
    {
        $html = $this->render('error.php', ['error' => $errorDetails]);
        return $this->createHtmlResponse($html, 500);
    }

    public function renderDefault(): ResponseInterface
    {
        $html = $this->render('default.php');
        return $this->createHtmlResponse($html);
    }

    private function createHtmlResponse(string $html, int $status = 200): ResponseInterface
    {
        return new Response(
            $status,
            ['Content-Type' => 'text/html; charset=utf-8'],
            $html
        );
    }

    private function resolveTemplatePath(string $templatePath): string
    {
        $basePathNormalized = $this->normalizePath($this->baseTemplatePath);
        $fullPath = realpath($basePathNormalized . DIRECTORY_SEPARATOR . $templatePath);
        $fullPathNormalized = $this->normalizePath($fullPath ?: '');

        if (empty($fullPathNormalized) || !str_starts_with($fullPathNormalized, $basePathNormalized)) {
            throw new RuntimeException("Invalid template path: {$templatePath}");
        }

        return $fullPathNormalized;
    }

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                continue;
            }
            $sanitized[$key] = $value;
        }
        return $sanitized;
    }
}
