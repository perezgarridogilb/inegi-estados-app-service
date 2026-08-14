<?php

namespace Cfdi;

use DOMDocument;
use DOMElement;
use stdClass;

class XmlBuilder
{
    use XmlTrait
;

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
        $this->comprobante->setAttribute('Descuento', $this->data->comprobante->exportacion ?? '01');        
        $this->comprobante->setAttribute('SubTotal', (string) ($this->data->comprobante->subtotal ?? '0'));
        $this->comprobante->setAttribute('Total', (string) ($this->data->comprobante->total ?? '0'));

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
            $node->setAttribute('ValorUnitario', ($concepto->valorUnitario ?? '0'));
            $node->setAttribute('Importe', $concepto->sutotal ?? '0');

            $node->setAttribute('ObjetoImp', (string) ($concepto->objetoImp ?? '02'));
            // $node->setAttribute('NoIdentificacion', (string) ($concepto->importe ?? '0'));
            $node->setAttribute('Descuento', '0');

            $hasTraslados = $concepto->objetoImp === '02' ? true : false;
            // $hasRetenciones = null;

            if ($hasTraslados) {
                $this->buildImpuestosConcepto($node, $concepto);
            }
            $this->conceptosNode->appendChild($node);
        }



        return $this;
    }

    /**
     * Helper para agregar el nodo <cfdi:Impuestos> dentro de un <cfdi:Concepto>
     */
    protected function buildImpuestosConcepto(DOMElement $conceptoNode, object $concepto): void
    {

        // if ($hasTraslados) {
        $concepto->traslados = new stdClass();
        $concepto->traslados->importe = $this->calculateTrasladoImporteFromBase((string) $concepto->importe, self::TASA_O_QUOTA);

        $impuestosNode = $this->doc->createElement('cfdi:Impuestos');
        $trasladosNode = $this->doc->createElement('cfdi:Traslados');

        $node = $this->doc->createElement('cfdi:Traslado');
        $node->setAttribute('Base', $concepto->importe);
        $node->setAttribute('Impuesto', '002'); // 002 = IVA
        $node->setAttribute('TipoFactor', 'Tasa');

        /**
         * Solo "02" = Sí objeto de impuesto obliga a desglosar Traslados/Retenciones.
         *
         * @see Guía de llenado CFDI - Documento relacionado con vales de despensa (SAT)
         * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=21
         */
        $node->setAttribute('TasaOCuota', self::TASA_O_QUOTA);
        $node->setAttribute('Importe', $concepto->traslados->importe);
        $trasladosNode->appendChild($node);
        $impuestosNode->appendChild($trasladosNode);

        $conceptoNode->appendChild($impuestosNode);
    }

    public function getXml(): string
    {
        return $this->doc->saveXML();
    }
}
