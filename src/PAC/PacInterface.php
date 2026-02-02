<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC;

interface PacInterface
{
    public function timbrar(string $xml): TimbradoResult;
}
