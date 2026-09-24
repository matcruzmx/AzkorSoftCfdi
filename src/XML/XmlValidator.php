<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\XML;

use DOMDocument;
use AzkorSoft\Cfdi\Exceptions\CfdiException;

final class XmlValidator
{
    public static function validate(string $xml, string $xsdPath): void
    {
        if (!file_exists($xsdPath)) {
            throw new CfdiException("Archivo de esquema XSD no encontrado: $xsdPath");
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        if ($dom->schemaValidate($xsdPath)) {
            libxml_clear_errors();
            return;
        }

        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!empty($errors)) {
            $msg = $errors[0]->message;
            throw new CfdiException("XML inválido contra XSD: $msg");
        }

        throw new CfdiException(
            "Error al validar contra esquema XSD, verifique que el archivo $xsdPath sea un XSD válido"
        );
    }
}
