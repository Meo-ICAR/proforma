<?php

namespace Tests\Unit;

use App\Models\SalesInvoice;
use App\Models\Client;
use App\Models\Clienti;
use App\Models\Provvigione;
use App\Services\SalesInvoiceMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoiceMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    private SalesInvoiceMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SalesInvoiceMatchingService();
    }

    /** @test */
    public function it_matches_sales_invoice_with_client_through_provvigione()
    {
        // Create test data
        $client = Client::factory()->create([
            'name' => 'Test Client SRL',
            'vat_number' => 'IT12345678901'
        ]);

        $provvigione = Provvigione::factory()->create([
            'denominazione_riferimento' => 'Test Client SRL',
            'piva' => 'IT12345678901',
            'n_fattura' => 'INV-001'
        ]);

        $salesInvoice = SalesInvoice::factory()->create([
            'customer_name' => 'Test Client SRL',
            'vat_number' => 'IT12345678901',
            'number' => 'INV-001'
        ]);

        // Mock the provvigione-client relationship
        $provvigione->client_id = $client->id;
        $provvigione->save();

        // Run matching
        $results = $this->service->matchSalesInvoices(['dry_run' => true]);

        $this->assertGreaterThan(0, $results['matched']);
        $this->assertArrayHasKey('details', $results);
    }

    /** @test */
    public function it_calculates_confidence_correctly()
    {
        $salesInvoice = new SalesInvoice([
            'customer_name' => 'Test Client',
            'vat_number' => 'IT12345678901'
        ]);

        $client = new Client([
            'name' => 'Test Client',
            'vat_number' => 'IT12345678901'
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateConfidence');
        $method->setAccessible(true);

        $confidence = $method->invoke($this->service, $salesInvoice, $client, 'direct');

        $this->assertEquals(1.0, $confidence);
    }

    /** @test */
    public function it_returns_matching_statistics()
    {
        // Create test data
        SalesInvoice::factory()->count(10)->create();
        SalesInvoice::factory()->create([
            'invoiceable_type' => 'App\\Models\\Client',
            'invoiceable_id' => 1
        ]);

        $stats = $this->service->getMatchingStats();

        $this->assertEquals(11, $stats['total_invoices']);
        $this->assertEquals(1, $stats['matched_invoices']);
        $this->assertEquals(10, $stats['unmatched_invoices']);
        $this->assertArrayHasKey('match_percentage', $stats);
    }

    /** @test */
    public function it_clears_matches()
    {
        // Create test data with matches
        SalesInvoice::factory()->create([
            'invoiceable_type' => 'App\\Models\\Client',
            'invoiceable_id' => 1
        ]);

        $cleared = $this->service->clearMatches();

        $this->assertEquals(1, $cleared);

        $invoice = SalesInvoice::first();
        $this->assertNull($invoice->invoiceable_type);
        $this->assertNull($invoice->invoiceable_id);
    }
}
