<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\XML;

use AzkorSoft\Cfdi\Cfdi40\Cfdi;
use AzkorSoft\Cfdi\Security\Certificado;

interface XmlBuilderInterface
{
    public function build(Cfdi $cfdi, Certificado $certificado): string;
}
