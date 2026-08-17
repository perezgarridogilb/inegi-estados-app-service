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

    /**
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=5
     */
    protected function dateGeneral(): string
    {
        $date = date('Y-m-d');
        $invoiceDate = $date . "T00:02:02";

        return $invoiceDate;
    }
    /**
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=19
     *
     * @param string $quantity Concept quantity
     * @param string $valUnit  Concept unit value
     * @return string Calculated amount
     */
    public function calculateImporteFromQuantity(
        string $quantity,
        string $valUnit
    ): string {
        return bcmul($quantity, $valUnit, 4);
    }

    /** (IVA transferred)
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=20
     *
     * @param string $base Taxable base (concept amount, after discount)
     * @param string $tasaOCuota Tax rate or quota (e.g. "0.160000")
     * @return string Calculated transfer amount
     */
    public function calculateTrasladoImporteFromBase(
        string $base,
        string $tasaOCuota
    ): string {
        return bcmul($base, $tasaOCuota, 4);
    }

    /**
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=7
     *
     * @param array $concepts Invoice concepts
     * @return string Invoice subtotal
     */
    public function calculateSubtotalFromDataGeneral(
        array $concepts,
    ): string {
        $subtotal = "0.00";
        foreach ($concepts as $concept) {
            $subtotal = bcadd($subtotal, $this->calculateImporteFromQuantity((string) $concept->cantidad, (string) $concept->valorUnitario), 4);
        }
        return $this->numberFormat2Dec($subtotal);
    }

    /**
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=24
     *
     * @param array $concepts Invoice concepts
     * @return array{0: string, 1: string} [total taxable base, total transferred taxes]
     */
    public function calculateTaxesAndAmount(
        array $concepts,
    ): array {
        $base = "0.00";
        $totalTrasladados = "0.00";

        foreach ($concepts as $concept) {
            $base = bcadd($base, (string) $concept->importe, 4);
            $totalTrasladados = bcadd($totalTrasladados, (string) $concept->traslados->importe, 4);
        }
        return [
            $this->numberFormat2Dec($base),
            $this->numberFormat2Dec($totalTrasladados)
        ];
    }
    /**
     * @link https://www.sat.gob.mx/minisitio/Factura/documentos/Guia_llenadoCFDI_DPA.pdf#page=8
     */
    public function calculateTotalFromDataGeneral(
        string $base,
        string $totalTrasladados
    ): string {
        return $this->numberFormat2Dec(
            bcadd($base, $totalTrasladados, 4)
        );
    }

    /**
     * Sums the given numbers and formats them with 2 decimal places.
     *
     * @param string $number
     * @return string
     */
    protected function numberFormat2Dec(string $number): string 
    {
        return bcadd($number, '0', 2);
    }
}
