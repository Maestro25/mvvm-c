<?php
declare(strict_types=1);

namespace App\Presentation\Controllers\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthControllerInterface
{
    public function login(ServerRequestInterface $request): ResponseInterface;

    public function register(ServerRequestInterface $request): ResponseInterface;

    public function logout(ServerRequestInterface $request): ResponseInterface;

    public function currentUserId(ServerRequestInterface $request): ResponseInterface;

    public function generateCsrfToken(ServerRequestInterface $request): ResponseInterface;

    public function isAuthenticated(ServerRequestInterface $request): ResponseInterface;

    public function validateCsrfToken(ServerRequestInterface $request): ResponseInterface;
}
