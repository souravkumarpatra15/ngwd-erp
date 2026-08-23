<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use DateTimeInterface;

class Cookie extends BaseConfig
{
    public string $prefix = '';
    public $expires = 0;
    public string $path = '/';
    public string $domain = '';

    // Session/auth cookies must never travel over plain HTTP in production.
    // Keep local development usable while enforcing Secure in production.
    public bool $secure = ENVIRONMENT === 'production';

    public bool $httponly = true;
    public string $samesite = 'Lax';
    public bool $raw = false;
}
