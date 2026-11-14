<?php

namespace Tests\Feature;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\ImportDigitalSales;
use App\Models\Business;
use App\Models\User;
use App\Services\Ai\GroqService;
use App\Services\DigitalSalesParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;
use ZipArchive;

class DigitalSalesParsingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kedai Kopi Niskala',
            'business_type' => 'fnb',
            'has_completed_onboarding' => true,
        ]);
    }

    public function test_parse_screenshot_via_mock_vision_ai_extracts_date_and_total_sales(): void
    {
        $mockGroq = Mockery::mock(GroqService::class);
        $mockGroq->shouldReceive('chat')
            ->once()
            ->andReturn([
                'platform' => 'gofood',
                'transaction_date' => '2026-11-13',
                'total_amount' => 320000,
                'gross_amount' => 400000,
                'fee_amount' => 80000,
                'net_amount' => 320000,
                'order_count' => 14,
                'merchant_name' => 'Kedai Kopi Niskala',
                'notes' => 'Rekap Penjualan GoFood 13 Nov 2026 - 14 Pesanan',
                'confidence_score' => 0.95,
                'needs_review' => false,
            ]);

        $this->app->instance(GroqService::class, $mockGroq);

        $parser = $this->app->make(DigitalSalesParserService::class);
        $file = UploadedFile::fake()->image('gofood_rekap.jpg', 600, 600);

        $result = $parser->parseScreenshot($file, 'gofood');

        $this->assertEquals('gofood', $result['platform']);
        $this->assertEquals('2026-11-13', $result['transaction_date']);
        $this->assertEquals(320000.0, $result['total_amount']);
        $this->assertEquals(400000.0, $result['gross_amount']);
        $this->assertEquals(80000.0, $result['fee_amount']);
        $this->assertEquals(14, $result['order_count']);
        $this->assertFalse($result['needs_review']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_parse_screenshot_with_blurry_image_flags_needs_review(): void
    {
        $mockGroq = Mockery::mock(GroqService::class);
        $mockGroq->shouldReceive('chat')
            ->once()
            ->andReturn([
                'platform' => 'qris',
                'transaction_date' => '2026-11-13',
                'total_amount' => 150000,
                'gross_amount' => 150000,
                'fee_amount' => 0,
                'confidence_score' => 0.55,
                'needs_review' => true,
                'review_reason' => 'Foto struk agak buram, angka nominal perlu diperiksa.',
            ]);

        $this->app->instance(GroqService::class, $mockGroq);

        $parser = $this->app->make(DigitalSalesParserService::class);
        $file = UploadedFile::fake()->image('qris_buram.png', 400, 400);

        $result = $parser->parseScreenshot($file, 'qris');

        $this->assertTrue($result['needs_review']);
        $this->assertEquals(0.55, $result['confidence_score']);
        $this->assertStringContainsString('buram', $result['review_reason']);
    }

    public function test_parse_csv_with_comma_delimiter_extracts_date_amount_and_rows(): void
    {
        $csvContent = <<<CSV
tanggal,keterangan,nominal,platform
2026-11-10,Pesanan Ayam Bakar,75000,gofood
2026-11-10,Pesanan Nasi Goreng,125000,gofood
2026-11-11,Pesanan Es Kopi Susu,100000,grabfood
CSV;

        $tempPath = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempPath, $csvContent);

        $parser = $this->app->make(DigitalSalesParserService::class);
        $result = $parser->parseCsv($tempPath, 'auto');

        unlink($tempPath);

        $this->assertEquals(300000.0, $result['total_amount']);
        $this->assertEquals(3, $result['order_count']);
        $this->assertCount(3, $result['rows']);
        $this->assertEquals('2026-11-11', $result['transaction_date']);
    }

    public function test_parse_csv_with_semicolon_delimiter_indonesian_excel_format(): void
    {
        $csvContent = <<<CSV
Tanggal;Platform;Keterangan;Total
12/11/2026;shopeefood;Pesanan Paket Hemat;50000
12/11/2026;shopeefood;Pesanan Paket Jumbo;85000
CSV;

        $tempPath = tempnam(sys_get_temp_dir(), 'csv_semi_');
        file_put_contents($tempPath, $csvContent);

        $parser = $this->app->make(DigitalSalesParserService::class);
        $result = $parser->parseCsv($tempPath, 'shopeefood');

        unlink($tempPath);

        $this->assertEquals(135000.0, $result['total_amount']);
        $this->assertEquals(2, $result['order_count']);
        $this->assertEquals('2026-11-12', $result['transaction_date']);
    }

    public function test_parse_csv_with_indonesian_currency_formatting(): void
    {
        $csvContent = <<<CSV
tanggal,nominal,keterangan
2026-11-12,"Rp 150.000",Order 1
2026-11-12,"50rb",Order 2
2026-11-12,"1.5jt",Order 3 Katering
CSV;

        $tempPath = tempnam(sys_get_temp_dir(), 'csv_curr_');
        file_put_contents($tempPath, $csvContent);

        $parser = $this->app->make(DigitalSalesParserService::class);
        $result = $parser->parseCsv($tempPath);

        unlink($tempPath);

        // 150.000 + 50.000 + 1.500.000 = 1.700.000
        $this->assertEquals(1700000.0, $result['total_amount']);
        $this->assertCount(3, $result['rows']);
    }

    public function test_parse_xlsx_extracts_sheet_data_natively(): void
    {
        $tempXlsx = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';

        $zip = new ZipArchive();
        $zip->open($tempXlsx, ZipArchive::CREATE);

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="xml" ContentType="application/xml"/>
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. xl/sharedStrings.xml
        $sharedStrings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="4" uniqueCount="4">
  <si><t>Tanggal</t></si>
  <si><t>Total</t></si>
  <si><t>2026-11-13</t></si>
  <si><t>Keterangan</t></si>
</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sharedStrings);

        // 3. xl/worksheets/sheet1.xml (Row 1: Header, Row 2: Data 2026-11-13 & 250000)
        $sheet1 = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
    <row r="1">
      <c r="A1" t="s"><v>0</v></c>
      <c r="B1" t="s"><v>1</v></c>
    </row>
    <row r="2">
      <c r="A2" t="s"><v>2</v></c>
      <c r="B2"><v>250000</v></c>
    </row>
  </sheetData>
</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

        $zip->close();

        $parser = $this->app->make(DigitalSalesParserService::class);
        $result = $parser->parseXlsx($tempXlsx, 'qris');

        unlink($tempXlsx);

        $this->assertEquals(250000.0, $result['total_amount']);
        $this->assertEquals('2026-11-13', $result['transaction_date']);
        $this->assertCount(1, $result['rows']);
    }

    public function test_livewire_parse_uploaded_file_transitions_to_review(): void
    {
        $this->actingAs($this->user);

        $mockParser = Mockery::mock(DigitalSalesParserService::class);
        $mockParser->shouldReceive('parseScreenshot')
            ->once()
            ->andReturn([
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'gofood',
                'transaction_date' => '2026-11-13',
                'total_amount' => 350000.0,
                'gross_amount' => 420000.0,
                'fee_amount' => 70000.0,
                'net_amount' => 350000.0,
                'order_count' => 15,
                'notes' => 'Rekap Penjualan GoFood 13 Nov 2026',
                'confidence_score' => 0.95,
                'needs_review' => false,
                'rows' => [
                    [
                        'date' => '2026-11-13',
                        'amount' => 350000.0,
                        'gross_amount' => 420000.0,
                        'fee_amount' => 70000.0,
                        'platform' => 'gofood',
                        'notes' => 'Rekap Penjualan GoFood 13 Nov 2026',
                    ],
                ],
            ]);

        $this->app->instance(DigitalSalesParserService::class, $mockParser);

        $file = UploadedFile::fake()->image('screenshot.png', 600, 600);

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('step', 'preview_file')
            ->call('parseUploadedFile')
            ->assertSet('step', 'review')
            ->assertSet('parsedData.total_amount', 350000.0)
            ->assertSet('parsedData.transaction_date', '2026-11-13')
            ->assertSee('Pratinjau Hasil Ekstraksi Penjualan')
            ->assertSee('350.000');
    }

    public function test_livewire_parse_uploaded_file_handles_ai_quota_exceeded(): void
    {
        $this->actingAs($this->user);

        $mockParser = Mockery::mock(DigitalSalesParserService::class);
        $mockParser->shouldReceive('parseScreenshot')
            ->once()
            ->andThrow(new AiQuotaExceededException('Kuota penuh'));

        $this->app->instance(DigitalSalesParserService::class, $mockParser);

        $file = UploadedFile::fake()->image('screenshot.png', 600, 600);

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->call('parseUploadedFile')
            ->assertSet('errorType', 'quota')
            ->assertSee('Kuota AI Anda Sedang Penuh')
            ->assertSee('Lanjut Catat Manual (Tanpa AI)');
    }

    public function test_livewire_proceed_to_emergency_review_allows_manual_input(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ImportDigitalSales::class)
            ->call('proceedToEmergencyReview')
            ->assertSet('step', 'review')
            ->assertSet('parsedData.needs_review', true)
            ->call('updateParsedAmount', '275000')
            ->assertSet('parsedData.total_amount', 275000.0)
            ->call('updateParsedDate', '2026-11-12')
            ->assertSet('parsedData.transaction_date', '2026-11-12');
    }
}

