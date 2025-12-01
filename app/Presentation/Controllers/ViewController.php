<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

abstract class ViewController
{
    /**
     * Render PHP template file with extracted data.
     *
     * @param string $viewFile Absolute path to PHP view.
     * @param array<string, mixed> $data Data to extract for template use.
     * @return string Rendered HTML content.
     */
    protected function render(string $viewFile, array $data = []): string
    {
        ob_start();
        extract($data, EXTR_SKIP);
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Render a named view (from Views directory) and wrap in a HtmlResponse.
     *
     * @param string $viewName View template filename without extension.
     * @param array<string, mixed> $data
     * @return ResponseInterface PSR-7 compatible HTML response.
     */
    protected function renderView(string $viewName, array $data = []): ResponseInterface
    {
        $viewFile = __DIR__ . '/../Views/' . $viewName . '.php';
        $htmlContent = $this->render($viewFile, $data);
        return new HtmlResponse($htmlContent);
    }

    /**
     * Handle exception by rendering error view with error message.
     *
     * @param \Throwable $e Exception to handle.
     * @param string $viewName View template filename for error rendering.
     * @return ResponseInterface Rendered error page response.
     */
    protected function handleException(\Throwable $e, string $viewName): ResponseInterface
    {
        return $this->renderView($viewName, ['error' => $e->getMessage()]);
    }
}
