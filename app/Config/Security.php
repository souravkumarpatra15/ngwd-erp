<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    public string $csrfProtection = 'cookie';

    // Keep the token stable for the lifetime of the CSRF cookie. This avoids
    // invalidating parallel browser requests while the cookie itself remains
    // protected by the framework's CSRF implementation.
    public bool $tokenRandomize = false;

    public string $tokenName = 'csrf_test_name';
    public string $headerName = 'X-CSRF-TOKEN';
    public string $cookieName = 'csrf_cookie_name';
    public int $expires = 7200;
    public bool $regenerate = true;
    public bool $redirect = (ENVIRONMENT === 'production');
}
