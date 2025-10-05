<?php
// app/Livewire/UserList.php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserList extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
    ];

    public function mount()
    {
        // Hanya CRS yang boleh akses
        if (Auth::user()->role !== 'CRS') {
            abort(403, 'Unauthorized access.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'role']);
        $this->resetPage();
    }

    public function deleteUser($userId)
    {
        // Hanya CRS yang boleh delete
        if (Auth::user()->role !== 'CRS') {
            abort(403, 'Unauthorized access.');
        }

        try {
            $user = User::findOrFail($userId);
            
            // Cegah delete diri sendiri
            if ($user->id === Auth::id()) {
                session()->flash('error', 'You cannot delete your own account.');
                return;
            }

            $userName = $user->name;
            $user->delete();
            
            session()->flash('success', "User {$userName} deleted successfully!");
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->role, function ($query) {
                $query->where('role', $this->role);
            })
            ->latest()
            ->paginate($this->perPage);

        $roles = ['CRS', 'Pengawas', 'CRO'];
        $stats = [
        'total_users' => User::count(),
        'crs_count' => User::where('role', 'CRS')->count(),
        'pengawas_count' => User::where('role', 'Pengawas')->count(),
        'cro_count' => User::where('role', 'CRO')->count(),
        ];

        return view('livewire.user-list', compact('users', 'roles', 'stats'));
    }
}