<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Main;
use Illuminate\Support\Facades\Storage;

class ItemObserver
{
    /**
     * Handle the item "created" event.
     *
     * @param \App\Item $item
     * @return void
     */
    //public function created(Item $item)
    public function created($item)
    {
        //
    }

    /**
     * Handle the item "updated" event.
     *
     * @param \App\Item $item
     * @return void
     */
    public function updated($item)
    {
        //
    }

    /**
     * Handle the item "deleted" event.
     *
     * @param \App\Item $item
     * @return void
     */
    public function deleting($item)
    {
        // предварительное удаление файлов с диска
        if ($item->base->type_is_photo() || $item->base->type_is_document()) {
            Storage::delete($item->filename());
        }
        $mains = Main::where('child_item_id', $item->id)->get();
        foreach ($mains as $main) {
            if ($main->parent_item->base->type_is_photo() || $main->parent_item->base->type_is_document()) {
                //Storage::delete($main->parent_item->filename());
                // нужно удалять, "Storage::delete()" выполнится, т.к. это рекурсивный вызов этой же функции "public function deleting($item)"
                $main->parent_item->delete();
            }
        }
    }

    /**
     * Handle the item "restored" event.
     *
     * @param \App\Item $item
     * @return void
     */
    public function restored($item)
    {
        //
    }

    /**
     * Handle the item "force deleted" event.
     *
     * @param \App\Item $item
     * @return void
     */
    public function forceDeleted($item)
    {
        //
    }
}
