<?php

namespace App\Livewire\Master;

use App\Models\PantryItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class PantryItemList extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $itemIdToDelete;

    protected $listeners = ['itemDeleted' => '$refresh'];

    public function confirmDelete($id)
    {
        $this->itemIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem()
    {
        $item = PantryItem::find($this->itemIdToDelete);
        if ($item) {
            // Delete the image from storage if it exists
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $item->delete();
        }

        $this->showDeleteModal = false;
        $this->dispatch('itemDeleted');
        session()->flash('message', 'Pantry item successfully deleted.');
    }

    public function render()
    {
        $query = PantryItem::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%')
;
            });
        }

        $items = $query->orderBy('name')->paginate(10);

        return view('livewire.master.pantry-item-list', [
            'items' => $items,
        ]);
    }
}