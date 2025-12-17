<?php

namespace App\Livewire\Meeting;

use Livewire\Component;
use App\Models\ExternalParticipant;

class SearchExternalParticipants extends Component
{
    public $search = '';
    public $selectedParticipants = [];

    public $showCreateForm = false;
    public $newName = '';
    public $newEmail = '';
    public $newCompany = '';
    public $newPhone = '';
    public $newAddress = '';

    public function mount($initialParticipants = [])
    {
        $this->selectedParticipants = $initialParticipants;
    }

    public function render()
    {
        $externalParticipants = collect();
        if (strlen($this->search) >= 2) {
            $externalParticipants = ExternalParticipant::where('name', 'like', '%' . $this->search . '%')
                                                     ->orWhere('email', 'like', '%' . $this->search . '%')
                                                     ->orWhere('company', 'like', '%' . $this->search . '%')
                                                     ->take(5)
                                                     ->get();
        }

        $selectedExternalParticipants = ExternalParticipant::whereIn('id', $this->selectedParticipants)->get();

        return view('livewire.meeting.search-external-participants', [
            'externalParticipants' => $externalParticipants,
            'selectedExternalParticipants' => $selectedExternalParticipants,
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
        $this->newCompany = '';
        $this->newPhone = '';
        $this->newAddress = '';
        $this->resetErrorBag();
    }

    public function createNewParticipant()
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newEmail' => 'nullable|email|unique:external_participants,email',
            'newCompany' => 'required|string|max:255',
            'newPhone' => 'required|string|max:20',
            'newAddress' => 'nullable|string|max:255',
        ]);

        $participant = ExternalParticipant::create([
            'name' => $this->newName,
            'email' => $this->newEmail,
            'company' => $this->newCompany,
            'phone' => $this->newPhone,
            'address' => $this->newAddress,
            'type' => 'external', // Default type
        ]);

        // Automatically add to selected
        $this->addParticipant($participant->id);
        
        // Reset form and UI
        $this->showCreateForm = false;
        $this->resetNewParticipantForm();
        
        // Clear search
        $this->search = '';
    }

    public function addParticipant($participantId)
    {
        if (!in_array($participantId, $this->selectedParticipants)) {
            $this->selectedParticipants[] = $participantId;
            $this->dispatch('external-participants-updated', $this->selectedParticipants);
        }
    }

    public function removeParticipant($participantId)
    {
        $this->selectedParticipants = array_diff($this->selectedParticipants, [$participantId]);
        $this->dispatch('external-participants-updated', $this->selectedParticipants);
    }
}