<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use App\Infrastructure\Core\Routing\UrlGenerator;
use App\Infrastructure\Core\Routing\RouteNames;

/**
 * AuthController renders Login and Register UI pages,
 * all action logic delegated to Coordinator layer.
 */
final class AuthViewController extends ViewController
{
    private LoggerInterface $logger;
    private UrlGenerator $urlGenerator;

    public function __construct(LoggerInterface $logger, UrlGenerator $urlGenerator)
    {
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Render the login page.
     *
     * @param array<string, mixed> $data Includes flashMessages, notifications, rememberMe
     * @return ResponseInterface
     */
    public function renderLoginPage(array $data): ResponseInterface
    {
        $data += [
            'flashMessages' => null,
            'notifications' => null,
            'rememberMe' => null,
            'resetPasswordUrl' => '#',
            'registerUrl' => '#',
        ];

        try {
            $data['resetPasswordUrl'] = $this->urlGenerator->generate(RouteNames::RESET_PASSWORD);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Failed to generate reset password URL: ' . $e->getMessage());
        }

        try {
            $data['registerUrl'] = $this->urlGenerator->generate(RouteNames::REGISTER);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Failed to generate register URL: ' . $e->getMessage());
        }

        return new HtmlResponse($this->renderTemplate('auth/login', $data));
    }

    public function renderRegisterPage(array $data): ResponseInterface
    {
        $data += [
            'flashMessages' => null,
            'notifications' => null,
            'loginUrl' => '#',
        ];

        try {
            $data['loginUrl'] = $this->urlGenerator->generate(RouteNames::LOGIN);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Failed to generate login URL: ' . $e->getMessage());
        }

        return new HtmlResponse($this->renderTemplate('auth/register', $data));
    }


    /**
     * Render a PHP template file under Views directory.
     *
     * @param string $template Relative path without .php extension
     * @param array<string, mixed> $data Variables for extraction in template
     *
     * @return string
     *
     * @throws \RuntimeException On missing or unreadable template
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
}
