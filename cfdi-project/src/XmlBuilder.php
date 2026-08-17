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
        $this->data->comprobante->total = "0.00";

        $this->comprobante->setAttribute('Serie', $this->data->comprobante->serie);
        $this->comprobante->setAttribute('Folio', $this->data->comprobante->folio);
        $this->comprobante->setAttribute('Fecha', $this->dateGeneral());
        $this->comprobante->setAttribute('FormaPago', $this->data->comprobante->formaPago);
        $this->comprobante->setAttribute('MetodoPago', $this->data->comprobante->metodoPago);
        $currency = $this->currencyByCode($this->data->comprobante->moneda);
        $this->comprobante->setAttribute('Moneda', $currency['code'] ?? 'MXN');
        // $this->comprobante->setAttribute('TipoCambio', (string) ($this->data->tipoCambio ?? '1'));
        $this->comprobante->setAttribute('TipoDeComprobante', $this->data->comprobante->tipoDeComprobante ?? 'I');
        $this->comprobante->setAttribute('LugarExpedicion', $this->data->comprobante->lugarExpedicion ?? '');
        $this->comprobante->setAttribute('Exportacion', '01');        
        $this->comprobante->setAttribute('Descuento', '0.00');        
        $this->comprobante->setAttribute('SubTotal', $this->data->comprobante->subtotal ?? '0.00');
        $this->comprobante->setAttribute('Total', $this->data->comprobante->total ?? '0.00');
        $this->comprobante->setAttribute('NoCertificado', '00001000000723626970');
        $this->comprobante->setAttribute('Certificado', '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');
        $this->comprobante->setAttribute('Sello', '');

        return $this;
    }

    public function buildEmisor(): self
    {
        $emisor = $this->data->emisor;
        $node = $this->doc->createElement('cfdi:Emisor');
        $node->setAttribute('Rfc', $emisor->rfc ?? '');
        $node->setAttribute('Nombre', $emisor->nombre ?? '');
        $node->setAttribute('RegimenFiscal', $emisor->regimenFiscal ?? '');

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
            $node->setAttribute('ClaveProdServ', $concepto->claveProdServ ?? '');
            $node->setAttribute('Cantidad', ($concepto->cantidad ?? ''));
            $node->setAttribute('ClaveUnidad', $concepto->claveUnidad ?? '');
            $node->setAttribute('Descripcion', $concepto->descripcion ?? '');
            $node->setAttribute('ValorUnitario', $concepto->valorUnitario);
            $node->setAttribute('Importe', $this->numberFormat2Dec($concepto->importe));
            $node->setAttribute('ObjetoImp', $concepto->objetoImp ?? '02');
            // $node->setAttribute('NoIdentificacion', (string) ($concepto->importe ?? '0.00'));
            $node->setAttribute('Descuento', '0.00');

            if (in_array($concepto->objetoImp, ['02'])) {
                $this->buildImpuestosConcepto($node, $concepto);
            }
            $this->conceptosNode->appendChild($node);
        }

        return $this;
    }

    /**
     * node <cfdi:Impuestos> in <cfdi:Concepto>
     */
    protected function buildImpuestosConcepto(DOMElement $conceptoNode, object $concepto): void
    {
        $concepto->traslados = new stdClass();
        $concepto->traslados->importe = $this->calculateTrasladoImporteFromBase((string) $concepto->importe, (string) $concepto->iva);

        $impuestosNode = $this->doc->createElement('cfdi:Impuestos');
        $trasladosNode = $this->doc->createElement('cfdi:Traslados');

        $node = $this->doc->createElement('cfdi:Traslado');
        $node->setAttribute('Base', $this->numberFormat2Dec($concepto->importe));
        $node->setAttribute('Impuesto', '002'); // 002 = IVA
        $node->setAttribute('TipoFactor', 'Tasa');

        /**
         * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=21
         */
        $node->setAttribute('TasaOCuota', $concepto->iva);
        $node->setAttribute('Importe', $this->numberFormat2Dec($concepto->traslados->importe));
        $trasladosNode->appendChild($node);
        $impuestosNode->appendChild($trasladosNode);
        $conceptoNode->appendChild($impuestosNode);
    }

    public function buildImpuestosComprobante(): self
    {
        list($base, $totalTrasladados) = array_fill(0, 2, '0.00');
        list($base, $totalTrasladados) = $this->calculateTaxesAndAmount($this->data->conceptos);
        
        $this->comprobante->setAttribute('Total', $this->calculateTotalFromDataGeneral($base, $totalTrasladados));

        $impuestosNode = $this->doc->createElement('cfdi:Impuestos');
        $impuestosNode->setAttribute('TotalImpuestosTrasladados', $totalTrasladados);
        $trasladosNode = $this->doc->createElement('cfdi:Traslados');

        $tasaCalculada = ($base > 0) ? ($totalTrasladados / $base) : 0;
        $tasaOCuotaFormatted = number_format($tasaCalculada, 6, '.', '');
        $node = $this->doc->createElement('cfdi:Traslado');
        $node->setAttribute('Base', $base);
        $node->setAttribute('Impuesto', '002');
        $node->setAttribute('TipoFactor', 'Tasa');
        $node->setAttribute('TasaOCuota', $tasaOCuotaFormatted);
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
