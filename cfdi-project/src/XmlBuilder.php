<?php

namespace Cfdi;

use DOMDocument;
use DOMElement;
use stdClass;

class XmlBuilder
{
    use XmlTrait;

    protected DOMDocument $doc;
    protected DOMElement $comprobante;
    protected ?DOMElement $conceptosNode = null;
    protected object $data;

    const TASA_O_QUOTA = '0.160000';

    protected array $namespaces = [
        'cfdi' => 'http://www.sat.gob.mx/cfd/4',
        'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    ];

    public function __construct(object $data)
    {
        $this->data = $data;
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;

        $this->initComprobante();
    }

    /**
     * Named Constructor to instance this Builder with the object data.
     */
    public static function make(object $data): self
    {
        return new self($data);
    }

    protected function initComprobante(): void
    {
        $this->comprobante = $this->doc->createElement('cfdi:Comprobante');
        $this->doc->appendChild($this->comprobante);

        $this->comprobante->setAttribute('xmlns:cfdi', $this->namespaces['cfdi']);
        $this->comprobante->setAttribute('xmlns:xsi', $this->namespaces['xsi']);
        $this->comprobante->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');
        $this->comprobante->setAttribute('Version', $this->data->comprobante->version);
    }

    public function buildDatosGenerales(): self
    {
        $this->data->comprobante->subtotal = $this->calculateSubtotalFromDataGeneral($this->data->conceptos);
        $this->data->comprobante->total = $this->calculateTotalFromDataGeneral($this->data->comprobante->subtotal, self::TASA_O_QUOTA);

        $this->comprobante->setAttribute('Serie', $this->data->comprobante->serie ?? 'A');
        $this->comprobante->setAttribute('Folio', $this->data->comprobante->folio ?? uniqid());
        $this->comprobante->setAttribute('Fecha', $this->dateGeneral());
        $this->comprobante->setAttribute('MetodoPago', $this->data->comprobante->metodoPago ?? 'PUE');
        $this->comprobante->setAttribute('FormaPago', $this->data->comprobante->formaPago ?? '01');
        $currency = $this->currencyByCode($this->data->comprobante->moneda);
        $this->comprobante->setAttribute('Moneda', $currency['code'] ?? 'MXN');
        // $this->comprobante->setAttribute('TipoCambio', (string) ($this->data->tipoCambio ?? '1'));
        $this->comprobante->setAttribute('TipoDeComprobante', $this->data->comprobante->tipoDeComprobante ?? 'I');
        $this->comprobante->setAttribute('LugarExpedicion', $this->data->comprobante->lugarExpedicion ?? '');
        $this->comprobante->setAttribute('Exportacion', '01');        
        $this->comprobante->setAttribute('Descuento', '0.00');        
        $this->comprobante->setAttribute('SubTotal', $this->data->comprobante->subtotal ?? '0.00');
        $this->comprobante->setAttribute('Total', $this->data->comprobante->total ?? '0.00');
        $this->comprobante->setAttribute('NoCertificado', '00001000000720371816');
        $this->comprobante->setAttribute('Certificado', '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" Sello="S3Rub6UieL7Rz0e88da43Oc/NynzYZrwTh9JrAmhQUm1BvhOJsI/I+oFfsbDGM3FioWe7yJlQJ74OJtQeQawfK9U3XTeYJG0gtZDyzNcDna6es7K5euxRWMBfmUgMcadFMNmrML3JmCH4LfNMYScuDgNrByAmYVpoDw309lQUbBtglbdBk0QKt7kpz5gT9+4UsZgKgssYsGG5cmmVHvn/95t5czUASxXr97ZB5zDO6AWRTslYh5fZT15JkbrzV5ukfe073FY3r3+4giNuHxUmxitfXWGZd4/Joe4tkeTujtWZ8hA9qG3py0kuLW92G8HwM/m2ZII7Z+r7Vtz/nC5tw==');
        $this->comprobante->setAttribute('Sello', '');

        return $this;
    }

    public function buildEmisor(): self
    {
        $emisor = $this->data->emisor;
        $node = $this->doc->createElement('cfdi:Emisor');
        $node->setAttribute('Rfc', $emisor->rfc ?? '');
        $node->setAttribute('Nombre', $emisor->nombre ?? '');
        $node->setAttribute('RegimenFiscal', $emisor->regimenFiscal ?? '601');

        $this->comprobante->appendChild($node);

        return $this;
    }

    public function buildReceptor(): self
    {
        $receptor = $this->data->receptor;
        $node = $this->doc->createElement('cfdi:Receptor');
        $node->setAttribute('Rfc', $receptor->rfc ?? '');
        $node->setAttribute('Nombre', $receptor->nombre ?? '');
        $node->setAttribute('DomicilioFiscalReceptor', $receptor->domicilioFiscalReceptor ?? '');
        $node->setAttribute('RegimenFiscalReceptor', $receptor->regimenFiscalReceptor ?? '601');
        $node->setAttribute('UsoCFDI', $receptor->usoCFDI ?? 'G01');

        $this->comprobante->appendChild($node);

        return $this;
    }

    public function buildConceptos(): self
    {
        if (!$this->conceptosNode) {
            $this->conceptosNode = $this->doc->createElement('cfdi:Conceptos');
            $this->comprobante->appendChild($this->conceptosNode);
        }

        foreach ($this->data->conceptos as $concepto) {

            $concepto->importe = $this->calculateImporteFromQuantity(
                (string) ($concepto->cantidad),
                (string) ($concepto->valorUnitario)
            );

            $node = $this->doc->createElement('cfdi:Concepto');
            $node->setAttribute('ClaveProdServ', $concepto->claveProdServ ?? '84111506');
            $node->setAttribute('Cantidad', ($concepto->cantidad ?? '1'));
            $node->setAttribute('ClaveUnidad', $concepto->claveUnidad ?? 'ACT');
            $node->setAttribute('Descripcion', $concepto->descripcion ?? '');
            $node->setAttribute('ValorUnitario', ($concepto->valorUnitario ?? '0.00'));
            $node->setAttribute('Importe', $concepto->importe ?? '0.00');
            $node->setAttribute('ObjetoImp', $concepto->objetoImp ?? '02');
            // $node->setAttribute('NoIdentificacion', (string) ($concepto->importe ?? '0.00'));
            $node->setAttribute('Descuento', '0.00');

            if (in_array($concepto->objetoImp, ['02'])) {
                $this->buildImpuestosConcepto($node, $concepto);
            }
            $this->conceptosNode->appendChild($node);
        }
        // $this->buildImpuestosComprobante();

        return $this;
    }

    /**
     * node <cfdi:Impuestos> in <cfdi:Concepto>
     */
    protected function buildImpuestosConcepto(DOMElement $conceptoNode, object $concepto): void
    {
        $concepto->traslados = new stdClass();
        $concepto->traslados->importe = $this->calculateTrasladoImporteFromBase((string) $concepto->importe, self::TASA_O_QUOTA);

        $impuestosNode = $this->doc->createElement('cfdi:Impuestos');
        $trasladosNode = $this->doc->createElement('cfdi:Traslados');

        $node = $this->doc->createElement('cfdi:Traslado');
        $node->setAttribute('Base', $this->numberFormat2Dec($concepto->importe));
        $node->setAttribute('Impuesto', '002'); // 002 = IVA
        $node->setAttribute('TipoFactor', 'Tasa');

        /**
         * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=21
         */
        $node->setAttribute('TasaOCuota', self::TASA_O_QUOTA);
        $node->setAttribute('Importe', $this->numberFormat2Dec($concepto->traslados->importe));
        $trasladosNode->appendChild($node);
        $impuestosNode->appendChild($trasladosNode);
        $conceptoNode->appendChild($impuestosNode);
    }

    public function buildImpuestosComprobante(): self
    {
        array_fill(0, 2, '0.00');
        list($base, $totalTrasladados) = $this->calculateTaxesAndAmount($this->data->conceptos);
        $impuestosNode = $this->doc->createElement('cfdi:Impuestos');
        $impuestosNode->setAttribute('TotalImpuestosTrasladados', $totalTrasladados);
        $trasladosNode = $this->doc->createElement('cfdi:Traslados');

        $node = $this->doc->createElement('cfdi:Traslado');
        $node->setAttribute('Base', $base);
        $node->setAttribute('Impuesto', '002');
        $node->setAttribute('TipoFactor', 'Tasa');
        $node->setAttribute('TasaOCuota', self::TASA_O_QUOTA);
        $node->setAttribute('Importe', $totalTrasladados);

        $trasladosNode->appendChild($node);
        $impuestosNode->appendChild($trasladosNode);
        $this->comprobante->appendChild($impuestosNode);

        return $this;
    }

    public function getXml(): string
    {
        return $this->doc->saveXML();
    }
}
