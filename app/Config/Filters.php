<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;
use App\Filters\AdminAuthFilter;
use App\Filters\ClientAuthFilter;

class Filters extends BaseConfig
{
    public array $aliases = [
        'adminauth'  => AdminAuthFilter::class,
        'clientauth' => ClientAuthFilter::class,
        'csrf'       => \CodeIgniter\Filters\CSRF::class,
        'toolbar'    => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'   => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars'=> \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders'=> \CodeIgniter\Filters\SecureHeaders::class,
    ];
    public array $globals = [
        'before' => ['csrf' => ['except' => ['webhook/*']]],
        'after'  => [],
    ];
    public array $methods = [];
    public array $filters = [];
}
