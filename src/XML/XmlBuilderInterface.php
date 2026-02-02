<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\XML;

use AzkorSoft\Cfdi\Cfdi40\Cfdi;

interface XmlBuilderInterface
{
    public function build(Cfdi $cfdi): string;
}
