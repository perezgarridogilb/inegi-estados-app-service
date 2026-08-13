# cfdi-project

Constructor de XML CFDI 4.0 usando `DOMDocument`.

## Estructura

```
cfdi-project/
├── composer.json        # Autoload PSR-4 (#Cfdi\ → src/)
├── src/
│   └── XmlBuilder.php   # Clase principal
├── index.php            # Script de ejecución/prueba
└── README.md
```

## Instalación

```bash
cd cfdi-project
composer install        # genera vendor/autoload.php
```

## Uso

```php
use Cfdi\XmlBuilder;

$builder = new XmlBuilder();

$comprobante = $builder->buildComprobante(
    ['Rfc' => 'XAXX010101000', 'Nombre' => 'Empresa Emisora SA', 'RegimenFiscal' => '601'],
    ['Rfc' => 'XAXX010101000', 'Nombre' => 'Receptor SA', 'UsoCFDI' => 'G01']
);

$builder->addConcepto($comprobante, [
    'ClaveProdServ' => '84111506',
    'Descripcion' => 'Servicio de prueba',
    'ValorUnitario' => '100.00',
    'Importe' => '100.00',
]);

echo $builder->toXml();
```

O probar en el navegador con `index.php`.