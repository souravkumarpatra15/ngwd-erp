<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL = 'http://localhost:8080/';
    public array $allowedHostnames = [];
    public string $indexPage = '';
    public string $uriProtocol = 'REQUEST_URI';
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';
    public string $defaultLocale = 'en';
    public bool $negotiateLocale = false;
    public array $supportedLocales = ['en'];
    public string $appTimezone = 'Asia/Kolkata';
    public string $charset = 'UTF-8';

    // Production should only serve authenticated/business traffic over HTTPS.
    public bool $forceGlobalSecureRequests = ENVIRONMENT === 'production';

    public array $proxyIPs = [];
    public bool $CSPEnabled = false;
}
