<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use App\Infrastructure\Core\Routing\UrlGenerator;
use App\Infrastructure\Core\Routing\RouteNames;
/**
 * MainViewController renders the main app shell layout and injects partial views.
 * Extends abstract ViewController for shared template and response utilities.
 */
final class MainViewController extends ViewController
{
    private LoggerInterface $logger;
    private UrlGenerator $urlGenerator;

    public function __construct(LoggerInterface $logger, UrlGenerator $urlGenerator)
    {
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Render the main shell layout, dynamically including content partial.
     *
     * @param array<string, mixed> $data Data for template including 'contentView'.
     *
     * @return ResponseInterface
     */
    public function renderShell(array $data): ResponseInterface
    {
        $data = array_merge([
            'pageTitle' => 'Application',
            'contentView' => 'main/content', // Default content partial
        ], $data);

        // Add canonical URL or other SEO URLs here if needed
        if (!isset($data['canonicalUrl']) && isset($data['contentView'])) {
            try {
                // Map content views to route names centrally
                $routeMap = [
                    'main/dashboard' => RouteNames::DASHBOARD,
                    'main/profile' => RouteNames::PROFILE,
                    'main/settings' => RouteNames::SETTINGS,
                ];

                $routeName = $routeMap[$data['contentView']] ?? RouteNames::DASHBOARD;

                $data['canonicalUrl'] = $this->urlGenerator->generate($routeName);
            } catch (\Throwable $e) {
                $this->logger->warning('Failed to generate canonical URL: ' . $e->getMessage());
                $data['canonicalUrl'] = '';
            }
        }

        return new HtmlResponse($this->renderTemplate('main/shell', $data));
    }

    /**
     * Render a PHP template file with given data and return the output string.
     *
     * @param string $template Relative path of template under views directory (without .php).
     * @param array<string, mixed> $data Variables to extract for template.
     *
     * @return string
     *
     * @throws \RuntimeException If template file is not found or unreadable.
     */
    protected function renderTemplate(string $template, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();

        $templatePath = __DIR__ . "/../Views/{$template}.php";

        if (!is_readable($templatePath)) {
            $this->logger->error("Template not found or unreadable: {$templatePath}");
            throw new \RuntimeException("Template {$template} not found.");
        }

        include $templatePath;

        return ob_get_clean();
    }

    /**
     * Render an error page within the main shell layout.
     *
     * @param string $errorMessage The error message to display.
     * @return ResponseInterface
     */
    public function renderErrorPage(string $errorMessage): ResponseInterface
    {
        $data = [
            'pageTitle' => 'Error',
            'contentView' => 'error/errorContent', // partial to display error message
            'errorMessage' => $errorMessage,
        ];
        return $this->renderShell($data);
    }
}
