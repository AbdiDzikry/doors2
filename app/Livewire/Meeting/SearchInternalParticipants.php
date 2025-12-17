<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\User;

class SearchInternalParticipants extends Component
{
    public $search = '';
    public $selectedParticipants = [];

    public $showCreateForm = false;
    public $newName = '';
    public $newEmail = '';
    public $newNpk = '';
    public $newDepartment = '';

    public function mount($initialParticipants = [])
    {
        $this->selectedParticipants = $initialParticipants;
    }

    public function updatedSearch($value)
    {
        \Illuminate\Support\Facades\Log::info('Livewire updatedSearch triggered. Value: ' . $value);
    }

    public function render()
    {
        $users = collect();
        if (strlen($this->search) >= 1) {
            \Illuminate\Support\Facades\Log::info('Searching for: ' . $this->search);
            $users = User::where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%')
                         ->orWhere('npk', 'like', '%' . $this->search . '%')
                         ->take(5)
                         ->get();
            \Illuminate\Support\Facades\Log::info('Found users: ' . $users->count());
        }

        $selectedUsers = User::whereIn('id', $this->selectedParticipants)->get();

        return view('livewire.meeting.search-internal-participants', [
            'users' => $users,
            'selectedUsers' => $selectedUsers,
        ]);
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->resetNewParticipantForm();
    }

    public function resetNewParticipantForm()
    {
        $this->newName = '';
        $this->newEmail = '';
        $this->newNpk = '';
        $this->newDepartment = '';
        $this->resetErrorBag();
    }

    public function createNewParticipant()
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newNpk' => 'required|string|unique:users,npk',
        ]);

        $user = User::create([
            'name' => $this->newName,
            'email' => null, // Email is optional now
            'npk' => $this->newNpk,
            'department' => null, // Department is optional/skipped
            'password' => bcrypt($this->newNpk), // Default password is NPK
        ]);

        $user->assignRole('Karyawan'); // Default role

        // Automatically add to selected
        $this->addParticipant($user->id);
        
        // Reset form and UI
        $this->showCreateForm = false;
        $this->resetNewParticipantForm();
        
        // Clear search so the list updates cleanly
        $this->search = ''; 
    }

    public function addParticipant($userId)
    {
        if (!in_array($userId, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $userId;
            $this->dispatch('internal-participants-updated', $this->selectedParticipants);
        }
    }

    public function removeParticipant($userId)
    {
        $this->selectedParticipants = array_diff($this->selectedParticipants, [$userId]);
        $this->dispatch('internal-participants-updated', $this->selectedParticipants);
    }
}