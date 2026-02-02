<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\XML;

use DOMDocument;
use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class XmlValidator
{
    public static function validate(string $xml, string $xsdPath): void
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        if (!$dom->schemaValidate($xsdPath)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            throw new CfdiException(
                'XML inválido contra XSD: ' . $errors[0]->message
            );
        }
    }
}
