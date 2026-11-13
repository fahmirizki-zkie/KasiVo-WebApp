<?php

namespace Tests\Feature;

use App\Livewire\ImportDigitalSales;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalSalesUploadTest extends TestCase
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
            'name' => 'Warung Kopi Niskala',
            'business_type' => 'fnb',
            'has_completed_onboarding' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('transactions.import-digital'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_business_is_redirected_to_onboarding(): void
    {
        $newUser = User::factory()->create();

        $this->actingAs($newUser);

        Livewire::test(ImportDigitalSales::class)
            ->assertRedirect(route('onboarding'));
    }

    public function test_authenticated_user_with_business_can_access_import_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.import-digital'));

        $response->assertOk();
        $response->assertSee('Impor Penjualan Digital');
        $response->assertSee('Pilih Screenshot atau Berkas Penjualan');
    }

    public function test_valid_screenshot_upload_is_accepted_as_screenshot_type(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('rekap_gobiz.png', 800, 600)->size(1500); // 1.5 MB

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('step', 'preview_file')
            ->assertSet('fileType', 'screenshot')
            ->assertSet('errorMessage', null)
            ->assertSet('fileMeta.name', 'rekap_gobiz.png')
            ->assertSet('fileMeta.extension', 'png')
            ->assertSee('Berkas Valid Berhasil Diterima')
            ->assertSee('rekap_gobiz.png');
    }

    public function test_valid_csv_spreadsheet_upload_is_accepted_as_spreadsheet_type(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('laporan_shopee.csv', 400, 'text/csv');

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('step', 'preview_file')
            ->assertSet('fileType', 'spreadsheet')
            ->assertSet('errorMessage', null)
            ->assertSet('fileMeta.name', 'laporan_shopee.csv')
            ->assertSet('fileMeta.extension', 'csv')
            ->assertSee('Berkas Valid Berhasil Diterima');
    }

    public function test_valid_xlsx_spreadsheet_upload_is_accepted_as_spreadsheet_type(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create(
            'mutasi_qris_bca.xlsx',
            600,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('step', 'preview_file')
            ->assertSet('fileType', 'spreadsheet')
            ->assertSet('errorMessage', null)
            ->assertSet('fileMeta.name', 'mutasi_qris_bca.xlsx')
            ->assertSet('fileMeta.extension', 'xlsx');
    }

    public function test_unsupported_file_format_is_rejected_with_clear_error_message(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('arsip_laporan.pdf', 500, 'application/pdf');

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('uploadedFile', null)
            ->assertSet('step', 'upload')
            ->assertSet('fileType', '')
            ->assertSee('Berkas Tidak Dapat Diterima');
    }

    public function test_screenshot_exceeding_size_limit_is_rejected(): void
    {
        $this->actingAs($this->user);

        // 6 MB (> 5 MB)
        $file = UploadedFile::fake()->image('screenshot_terlalu_besar.jpg', 2000, 2000)->size(6 * 1024);

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('uploadedFile', null)
            ->assertSet('step', 'upload')
            ->assertSee('Ukuran tangkapan layar melebihi batas maksimum 5 MB');
    }

    public function test_document_exceeding_size_limit_is_rejected(): void
    {
        $this->actingAs($this->user);

        // 12 MB (> 10 MB)
        $file = UploadedFile::fake()->create('data_raksasa.csv', 12 * 1024, 'text/csv');

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('uploadedFile', null)
            ->assertSet('step', 'upload')
            ->assertSee('Ukuran berkas dokumen melebihi batas maksimum 10 MB');
    }

    public function test_user_can_remove_uploaded_file_and_reset_state(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('rekap.png', 400, 400)->size(500);

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('step', 'preview_file')
            ->call('removeFile')
            ->assertSet('uploadedFile', null)
            ->assertSet('fileMeta', [])
            ->assertSet('fileType', '')
            ->assertSet('step', 'upload')
            ->assertSet('errorMessage', null);
    }

    public function test_user_can_select_platform(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ImportDigitalSales::class)
            ->assertSet('platform', 'auto')
            ->call('setPlatform', 'gofood')
            ->assertSet('platform', 'gofood')
            ->call('setPlatform', 'qris')
            ->assertSet('platform', 'qris')
            ->call('setPlatform', 'invalid_platform')
            ->assertSet('platform', 'qris'); // tetap qris karena invalid_platform ditolak
    }

    public function test_proceed_to_parsing_transitions_step_when_file_is_present(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('grab_omzet.jpg', 600, 600)->size(800);

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->assertSet('step', 'preview_file')
            ->call('proceedToParsing')
            ->assertSet('step', 'parsing')
            ->assertSee('Sedang Mengekstrak Data Penjualan Digital');
    }

    public function test_proceed_to_parsing_fails_gracefully_if_no_file_present(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ImportDigitalSales::class)
            ->call('proceedToParsing')
            ->assertSet('step', 'upload')
            ->assertSee('Silakan pilih berkas tangkapan layar');
    }
}

