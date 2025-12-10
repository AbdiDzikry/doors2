<?php

namespace App\Livewire\Master;

use App\Models\ExternalParticipant;
use Livewire\Component;
use Livewire\WithPagination;

class ExternalParticipantList extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $participantIdToDelete;

    protected $listeners = ['participantDeleted' => '$refresh'];

    public function confirmDelete($id)
    {
        $this->participantIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteParticipant()
    {
        $participant = ExternalParticipant::find($this->participantIdToDelete);
        if ($participant) {
            $participant->delete();
        }

        $this->showDeleteModal = false;
        $this->dispatch('participantDeleted');
        session()->flash('message', 'Participant successfully deleted.');
    }

    public function render()
    {
        $query = ExternalParticipant::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('company', 'like', '%' . $this->search . '%')
                  ->orWhere('department', 'like', '%' . $this->search . '%');
            });
        }

        $participants = $query->orderBy('name')->paginate(10);

        return view('livewire.master.external-participant-list', [
            'participants' => $participants,
        ]);
    }
}