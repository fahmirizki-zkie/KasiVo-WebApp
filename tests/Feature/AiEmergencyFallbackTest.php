<?php

namespace Tests\Feature;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\CreateExpensePhotoTransaction;
use App\Livewire\CreateExpenseTextTransaction;
use App\Livewire\CreateExpenseVoiceTransaction;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Ai\GroqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AiEmergencyFallbackTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kedai Kopi KasiVo',
            'business_type' => 'food_beverage',
        ]);
    }

    public function test_photo_transaction_shows_quota_full_alert_when_ai_limit_exceeded(): void
    {
        Storage::fake('public');

        $mockGroq = Mockery::mock(GroqService::class);
        $mockGroq->shouldReceive('extractFromImage')
            ->once()
            ->andThrow(new AiQuotaExceededException('Kuota AI Anda sudah penuh atau token AI Anda sudah habis.'));

        $this->app->instance(GroqService::class, $mockGroq);

        $fakePhoto = UploadedFile::fake()->image('nota_belanja.jpg', 600, 800);

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->set('photo', $fakePhoto)
            ->call('processPhoto')
            ->assertSet('errorType', 'quota_exceeded')
            ->assertSee('Kuota AI Anda Sudah Penuh / Token AI Anda Sudah Habis')
            ->assertSee('Koreksi Nominal Langsung');
    }

    public function test_photo_emergency_review_allows_manual_save_without_losing_photo(): void
    {
        Storage::fake('public');
        $fakePhoto = UploadedFile::fake()->image('nota_darurat.jpg', 600, 800);

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->set('photo', $fakePhoto)
            ->call('proceedToEmergencyReview')
            ->assertSet('step', 'review')
            ->assertSet('draft.needs_review', true)
            ->set('draft.amount', 80000)
            ->set('draft.category', 'bahan_baku')
            ->call('saveTransaction')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'amount' => 80000,
            'source_type' => 'photo',
        ]);
    }

    public function test_text_transaction_shows_quota_full_alert_and_emergency_fallback(): void
    {
        $mockGroq = Mockery::mock(GroqService::class);
        $mockGroq->shouldReceive('extractFromText')
            ->once()
            ->andThrow(new AiQuotaExceededException('Kuota AI Anda sudah penuh atau token AI Anda sudah habis.'));

        $this->app->instance(GroqService::class, $mockGroq);

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('rawText', 'Beli telur 2kg 50rb')
            ->call('processText')
            ->assertSet('errorType', 'quota_exceeded')
            ->assertSee('Kuota AI Anda Sudah Penuh / Token AI Anda Sudah Habis')
            ->call('proceedToEmergencyReview')
            ->assertSet('step', 'review')
            ->set('draft.amount', 50000)
            ->call('saveTransaction')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'amount' => 50000,
            'source_type' => 'text',
        ]);
    }

    public function test_voice_transaction_emergency_review_fallback(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateExpenseVoiceTransaction::class)
            ->set('transcriptionText', 'Beli minyak 2L 35rb')
            ->call('proceedToEmergencyReview')
            ->assertSet('step', 'review')
            ->set('draft.amount', 35000)
            ->call('saveTransaction')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'amount' => 35000,
            'source_type' => 'voice',
        ]);
    }
}
