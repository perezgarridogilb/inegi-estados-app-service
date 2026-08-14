<?php

namespace Cfdi\Services;

use DOMDocument;
use LibXMLError;

class CfdiXsdValidator
{
    /**
     * Validates an XML string against a CFDI 4.0 XSD schema.
     *
     * @param string $xml XML content
     * @param string $xsdPathOrUrl Local path or URL of the XSD (e.g. https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd)
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(string $xml, string $xsdPathOrUrl): array
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        libxml_use_internal_errors(true);
        if ($doc->schemaValidate($xsdPathOrUrl)) {
            libxml_clear_errors();
            return ['valid' => true, 'errors' => []];
        }

        $errors = array_map(
            fn (LibXMLError $e) => trim($e->message),
            libxml_get_errors()
        );
        libxml_clear_errors();

        return ['valid' => false, 'errors' => $errors];
    }
}
