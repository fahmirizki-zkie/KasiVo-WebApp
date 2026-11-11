<?php

namespace App\Livewire;

use App\Models\Business;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateExpenseVoiceTransaction extends Component
{
    use WithFileUploads;

    public string $sourceType = 'voice';

    /**
     * Ambil profil usaha aktif milik pengguna yang sedang terautentikasi.
     */
    protected function getActiveBusiness(): ?Business
    {
        return auth()->user()?->businesses()->first();
    }

    public function render()
    {
        $business = $this->getActiveBusiness();

        return view('livewire.create-expense-voice-transaction', [
            'hasBusiness' => (bool) $business,
            'business' => $business,
        ]);
    }
}

