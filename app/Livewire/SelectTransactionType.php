<?php

namespace App\Livewire;

use App\Models\Business;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SelectTransactionType extends Component
{
    public function render()
    {
        $business = auth()->user()?->businesses()->first();

        return view('livewire.select-transaction-type', [
            'business' => $business,
        ]);
    }
}

