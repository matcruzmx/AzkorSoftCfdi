<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC;

final class TimbradoResult
{
    public function __construct(
        public string $uuid,
        public string $xmlTimbrado,
        public string $fechaTimbrado,
        public string $selloSat,
        public string $noCertificadoSat
    ) {}
}
