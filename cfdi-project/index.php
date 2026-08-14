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
 * @param array|null $argv Command line arguments
 * @return int Exit code
 */
function main(?array $argv): int
{
    if (PHP_SAPI === 'cli') {
        $options = getopt("", ["path:"], $restIndex);
        $path = $options['path'] ?? null;
    } else {
        $path = $_GET['path'] ?? null;
    }

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

    try {
        $data = json_decode($jsonContent, false, 512, JSON_THROW_ON_ERROR);

        $xml = XmlBuilder::make($data)
            ->buildDatosGenerales()
            ->buildEmisor()
            ->buildReceptor()
            ->buildConceptos()
            ->buildImpuestosComprobante();

        $filePath = __DIR__ . '/cfdi_generado.xml';

        if (file_put_contents($filePath, $xml->getXml()) === false) {
            throw new Exception("No se pudo guardar el archivo en la ruta: {$filePath}");
        }

        echo "Archivo XML guardado exitosamente en: {$filePath}\n";

        $validator = new CfdiXsdValidator();
        $result = $validator->validate($xml->getXml(), 'https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');

        if ($result['valid']) {
            echo "Validación XSD: OK\n";
            return 0;
        }

        echo "Validación XSD: FALLÓ\n";
        foreach ($result['errors'] as $error) {
            echo "  - {$error}\n";
        }
        return 1;
    } catch (\JsonException $e) {
        echo "Error de sintaxis o estructura en el JSON: " . $e->getMessage() . "\n";
        return 1;
    } catch (\Throwable $e) {
        echo "Error inesperado durante el procesamiento: " . $e->getMessage() . "\n";
        return 1;
    }
}

exit(main($argv ?? null));
