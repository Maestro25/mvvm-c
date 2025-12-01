<?php
declare(strict_types=1);

namespace App\Presentation\Shared\Views;

use Psr\Http\Message\ResponseInterface;

interface ViewRendererInterface
{
    /**
     * Set the global layout template file path (e.g. 'layout.php').
     */
    public function setLayout(string $layoutTemplate): void;

    /**
     * Optionally set global data available to all templates.
     */
    public function setGlobalData(array $data): void;

    /**
     * Render a full content page wrapped in layout and partials, returning PSR-7 Response.
     * 
     * @param array $data Page-level view data.
     * @param array $partials Key=>template partials to render and inject.
     */
    public function renderContent(array $data = [], array $partials = []): ResponseInterface;

    /**
     * Render a partial template without layout, useful for AJAX or dynamic updates.
     * 
     * @param string $templatePath Path to template relative to base.
     * @param array $data Partial-level view data.
     */
    public function renderPartial(string $templatePath, array $data = []): ResponseInterface;

    /**
     * Render any template to a raw string. Throws if template file error.
     * 
     * @param string $templatePath Path relative to base.
     * @param array $data View data.
     * @return string Rendered template HTML.
     */
    public function render(string $templatePath, array $data = []): string;

    /**
     * Optional convenience methods to render loading or error templates with full response.
     */
    public function renderLoading(): ResponseInterface;

    public function renderErrorTemplate(array $errorDetails): ResponseInterface;

    public function renderDefault(): ResponseInterface;
}
