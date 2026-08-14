<?php

namespace Cfdi;

trait XmlTrait
{
    protected function currencyByCode(string $code): ?array
    {
        $currencies = require __DIR__ . '/config/cat_currencies.php';

        foreach ($currencies as $currency) {
            if ($currency['code'] === $code) {
                return $currency;
            }
        }

        return null;
    }

    protected function dateGeneral(): string
    {
        $date = date('Y-m-d');
        $invoiceDate = $date . "T00:02:02";

        return $invoiceDate;
    }
    /**
     * @see Guía de llenado CFDI - Estándar de comprobante fiscal digital
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=19
     *
     * @param string $quantity Cantidad del concepto
     * @param string $valUnit  Valor unitario del concepto
     * @return string Importe calculado
     */
    public function calculateImporteFromQuantity(
        string $quantity,
        string $valUnit
    ): string {
        return bcmul($quantity, $valUnit, 4);
    }

    /** (IVA trasladado)
     * @see Guía de llenado CFDI - Estándar de comprobante fiscal digital
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=19
     *
     * @param string $base Base gravable (importe del concepto, después de descuento)
     * @param string $tasaOCuota Tasa o cuota del impuesto (ej. "0.160000")
     * @return string Importe del traslado calculado
     */
    public function calculateTrasladoImporteFromBase(
        string $base,
        string $tasaOCuota
    ): string {
        return bcmul($base, $tasaOCuota, 4);
    }

    /**
     * @see Guía de llenado CFDI - Estándar de comprobante fiscal digital
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=7
     *
     * @param string $base Base gravable (importe del concepto, después de descuento)
     * @param string $tasaOCuota Tasa o cuota del impuesto (ej. "0.160000")
     * @return string Importe del traslado calculado
     */
public function calculateSubtotalFromDataGeneral(
        array $concepts,
    ): string {
        $subtotal = [];
        foreach ($concepts as $concept) {
            $concept->subtotal = $this->calculateImporteFromQuantity((string) $concept->cantidad, (string) $concept->valorUnitario);
            $subtotal[] = $concept->subtotal;
        }
        return $this->numberFormat2Dec(...$subtotal);
    }

    /**
     * @see Guía de llenado CFDI - Estándar de comprobante fiscal digital
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=24
     *
     * */
    public function calculateTaxesAndAmount(
        array $concepts,
    ): array {
        $base = [];
        $totalTrasladados = [];

        foreach ($concepts as $concept) {
            $base[] = $concept->importe;
            $totalTrasladados[] = $concept->traslados->importe;
        }
        return [
            $this->numberFormat2Dec(...$base),
            $this->numberFormat2Dec(...$totalTrasladados)
        ];
    }
    /**
     * @see Guía de llenado CFDI - Estándar de comprobante fiscal digital
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=8
     *
     * */
    public function calculateTotalFromDataGeneral(
        string $importe,
        string $tasaOCuota
    ): string {
        return bcmul($importe, bcadd($tasaOCuota, "1", 6), 2);
    }

    /**
     * Format numbers with 2 decimal places
     * 
     * @param string ...$numbers
     * @return string
     */
    protected function numberFormat2Dec(
        string ...$numbers
    ): string {
        $sum = '0';

        foreach ($numbers as $n) {
            $sum = bcadd($sum, $n, 4);
        }

        return number_format((float)$sum, 2, '.', '');
    }
}
