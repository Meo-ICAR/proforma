<?php

namespace App\Services;

/**
 * Punto unico dell'abbinamento delle provvigioni alle fatture. Un proforma è composto da più provvigioni di dettaglio; a seconda del tipo
 * di provvigione la fattura è diversa e si arriva ad essa per strade diverse:
 *
 *   Agente   → proforma del fornitore → fattura di acquisto (stessa partita IVA del fornitore, fattura non precedente al proforma);
 *   Istituto → proforma dell'istituto (clientis) → fattura di vendita (stessa partita IVA, cliente non fittizio, fattura non precedente);
 *   Cliente  → senza proforma: codice fiscale della pratica → cliente (`clients.tax_code`) → fattura di vendita a persona (nome e importo).
 *
 * Fattura emessa = `Pagato`: ogni abbinamento porta a `Pagato` le provvigioni coinvolte. Una fattura già abbinata (a un proforma o a una
 * provvigione) non si riusa.
 */
class InvoiceMatchingService
{
    public function __construct(
        private readonly ProformaPurchaseInvoiceMatchingService $agenti,
        private readonly ProformaInvoiceMatchingService $istituti,
        private readonly ProvvigioneInvoiceMatchingService $clienti,
    ) {}

    /**
     * @param  array<int, string>  $tipi  agente, istituto, cliente (default: tutti)
     * @return array{agente?: array, istituto?: array, cliente?: array}
     */
    public function match(array $tipi = ['agente', 'istituto', 'cliente']): array
    {
        $results = [];

        if (in_array('agente', $tipi, true)) {
            $results['agente'] = $this->agenti->matchProformasToInvoices();
        }

        if (in_array('istituto', $tipi, true)) {
            $results['istituto'] = $this->istituti->matchProformasToInvoices();
        }

        if (in_array('cliente', $tipi, true)) {
            $results['cliente'] = $this->clienti->matchClienteProvvigioniToSalesInvoices();
        }

        return $results;
    }

    /** Fatture di vendita: gli istituti passano dal proforma, i clienti no. */
    public function matchVendite(): array
    {
        return $this->match(['istituto', 'cliente']);
    }

    /** Fatture di acquisto: i fornitori (agenti) passano dal proforma. */
    public function matchAcquisti(): array
    {
        return $this->match(['agente']);
    }

    /** @return array{agente: array, istituto: array, cliente: array} Quanto resta da abbinare, per tipo */
    public function statistics(): array
    {
        return [
            'agente' => $this->agenti->getUnmatchedStatistics(),
            'istituto' => $this->istituti->getUnmatchedStatistics(),
            'cliente' => $this->clienti->getUnmatchedStatistics(),
        ];
    }
}
