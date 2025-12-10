<?php

namespace App\Livewire\Master;

use App\Models\Room;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class RoomList extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $roomIdToDelete;

    protected $listeners = ['roomDeleted' => '$refresh'];

    public function confirmDelete($id)
    {
        $this->roomIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRoom()
    {
        $room = Room::find($this->roomIdToDelete);
        if ($room) {
            // Delete the image from storage if it exists
            if ($room->image_path) {
                Storage::disk('public')->delete($room->image_path);
            }
            $room->delete();
        }

        $this->showDeleteModal = false;
        $this->dispatch('roomDeleted');
        session()->flash('message', 'Room successfully deleted.');
    }

    public function render()
    {
        $now = now(); // Get current time once

        $query = Room::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('floor', 'like', '%' . $this->search . '%')
                  ->orWhere('status', 'like', '%' . $this->search . '%');
            });
        }

        $rooms = $query->with(['meetings' => function ($q) use ($now) {
            $q->where('start_time', '<=', $now)
              ->where('end_time', '>=', $now)
              ->where('status', '!=', 'cancelled');
        }])
        ->orderBy('name')->paginate(10);

        // Add a dynamic attribute to each room indicating if it's currently in use
        $rooms->each(function ($room) {
            $room->is_in_use = $room->meetings->isNotEmpty();
        });

        return view('livewire.master.room-list', [
            'rooms' => $rooms,
        ]);
    }
}