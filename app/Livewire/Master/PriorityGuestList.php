<?php

namespace App\Livewire\Master;

use App\Models\PriorityGuest;
use Livewire\Component;
use Livewire\WithPagination;

class PriorityGuestList extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $guestIdToDelete;

    protected $listeners = ['guestDeleted' => '$refresh'];

    public function confirmDelete($id)
    {
        $this->guestIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteGuest()
    {
        $guest = PriorityGuest::find($this->guestIdToDelete);
        if ($guest) {
            $guest->delete();
        }

        $this->showDeleteModal = false;
        $this->dispatch('guestDeleted');
        session()->flash('message', 'Priority guest successfully deleted.');
    }

    public function render()
    {
        $query = PriorityGuest::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $guests = $query->orderBy('level', 'desc')->orderBy('name')->paginate(10);

        return view('livewire.master.priority-guest-list', [
            'guests' => $guests,
        ]);
    }
}