<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Cfdi\Services\CfdiXsdValidator;
use Cfdi\XmlBuilder;

/**
 * main function
 *
 * CFDI XML and writes it to disk.
 *
 * @param array $argv Command line arguments
 * @return int Exit code
 */
function main(array $argv): int
{
    $options = getopt("", ["path:"], $restIndex);

    $path = $options['path'] ?? null;

    if (!$path) {
        echo "Error: El parámetro --path es obligatorio.\n";
        echo "Uso: php index.php --path=/ruta/al/archivo.json\n";
        return 1;
    }

    echo "Procesando archivo en: " . $path . "\n";

    $jsonContent = file_get_contents($path);

    if ($jsonContent === false) {
        echo "Error: No se pudo leer el archivo en '{$path}'. Verifica los permisos.\n";
        return 1;
    }

    $data = json_decode($jsonContent, false);
    $xml = XmlBuilder::make($data)
        ->buildDatosGenerales()
        ->buildEmisor()
        ->buildReceptor()
        ->buildConceptos();

    $filePath = __DIR__ . '/cfdi_generado.xml';
    if (file_put_contents($filePath, $xml->getXml()) !== false) {
        echo "Archivo XML guardado exitosamente en: {$filePath}\n";

        $validator = new CfdiXsdValidator();
        $result = $validator->validate($xml->getXml(), 'https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');

        if ($result['valid']) {
            echo "Validación XSD: OK\n";
        } else {
            echo "Validación XSD: FALLÓ\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error}\n";
            }
        }
        return 0;
    }

    echo "Error al guardar el archivo XML. Revisa permisos de escritura.\n";
    return 1;
}

exit(main($argv));