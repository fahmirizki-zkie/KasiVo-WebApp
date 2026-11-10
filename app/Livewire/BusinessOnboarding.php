<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BusinessOnboarding extends Component
{
    public string $name = '';
    public string $business_type = '';
    public string $description = '';

    protected array $rules = [
        'name' => 'required|string|min:3|max:255',
        'business_type' => 'required|string|max:100',
        'description' => 'nullable|string|max:1000',
    ];

    protected array $messages = [
        'name.required' => 'Nama usaha wajib diisi.',
        'name.min' => 'Nama usaha minimal 3 karakter.',
        'business_type.required' => 'Jenis usaha wajib diisi (misal: Kedai Kopi, Pecel Lele).',
    ];

    public function save()
    {
        $this->validate();

        // Hubungkan langsung ke model Business (OOP)
        auth()->user()->businesses()->create([
            'name' => $this->name,
            'business_type' => $this->business_type,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Profil usaha berhasil disimpan!');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.business-onboarding');
    }
}
