<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC\Providers;

use Matcruz\AzkorSoft\Config\Config;

final class SwPacFactory
{
    public static function make(): SwPac
    {
        $config = Config::get('PAC_SW');

        $modo = $config['modo']; // test | prod
        $env  = $config[$modo];

        return new SwPac(
            url: $env['url'],
            token: $env['token'],
            user: $env['user'],
            password: $env['password'],
            produccion: $modo === 'prod'
        );
    }
}
