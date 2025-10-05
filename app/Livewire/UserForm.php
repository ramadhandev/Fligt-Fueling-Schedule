<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm extends Component
{
    public $user;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role = 'CRO';
    public $phone_number;
    // HAPUS: public $is_whatsapp_opt_in = false;

    public $isEditing = false;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->user?->id)
            ],
            'role' => 'required|in:CRS,Pengawas,CRO',
            'phone_number' => 'nullable|string|max:20',
            // HAPUS: 'is_whatsapp_opt_in' => 'boolean',
        ];

        if (!$this->isEditing || $this->password) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    public function mount($userId = null)
    {
        // Hanya CRS yang boleh akses
        if (Auth::user()->role !== 'CRS') {
            abort(403, 'Unauthorized access.');
        }

        if ($userId) {
            $this->user = User::findOrFail($userId);
            $this->isEditing = true;
            
            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->role = $this->user->role;
            $this->phone_number = $this->user->phone_number;
            // HAPUS: $this->is_whatsapp_opt_in = $this->user->is_whatsapp_opt_in;
        }
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone_number' => $this->phone_number,
            // HAPUS: 'is_whatsapp_opt_in' => $this->is_whatsapp_opt_in,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            $this->user->update($userData);
            session()->flash('success', 'User updated successfully!');
        } else {
            User::create($userData);
            session()->flash('success', 'User created successfully!');
        }

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.user-form', [
            'roles' => ['CRS', 'Pengawas', 'CRO']
        ]);
    }
}