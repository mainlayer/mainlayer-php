<?php

declare(strict_types=1);

namespace Mainlayer\Resources;

use Mainlayer\Exception\MainlayerException;
use Mainlayer\HttpClient;

/**
 * Authentication operations for Mainlayer accounts.
 */
class AuthResource
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Register a new Mainlayer account.
     *
     * @param array{
     *     email: string,
     *     password: string,
     * } $params
     *
     * @return array<string, mixed> Response containing access_token and user_id
     *
     * @throws MainlayerException
     */
    public function register(array $params): array
    {
        return $this->http->post('/auth/register', $params);
    }

    /**
     * Login to a Mainlayer account and receive an access token.
     *
     * @param array{
     *     email: string,
     *     password: string,
     * } $params
     *
     * @return array<string, mixed> Response containing access_token
     *
     * @throws MainlayerException
     */
    public function login(array $params): array
    {
        return $this->http->post('/auth/login', $params);
    }
}
