# cfdi-project

Constructor de XML CFDI 4.0 usando `DOMDocument`.

## Estructura

```
cfdi-project/
├── composer.json        # Autoload (src/)
├── src/
│   └── XmlBuilder.php
├── index.php            # Script de ejecución/prueba
└── README.md
```

## Instalación

```bash
cd cfdi-project
composer install        # genera vendor/autoload.php
```

## Uso

```bash
php index.php --path=enegence.json
```

O probar en el navegador con `index.php`.

Formato de datos de ejemplo: `enegence.json`.
