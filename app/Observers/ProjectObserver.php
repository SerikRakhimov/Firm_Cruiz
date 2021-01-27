<?php

namespace App\Observers;

use App\Models\Item;
use App\Project;
use Illuminate\Support\Facades\Storage;

class ProjectObserver
{
    /**
     * Handle the project "created" event.
     *
     * @param \App\Project $project
     * @return void
     */
    public function created($project)
    {
        //
    }

    /**
     * Handle the project "updated" event.
     *
     * @param \App\Project $project
     * @return void
     */
    public function updated($project)
    {
        //
    }

    /**
     * Handle the project "deleted" event.
     *
     * @param \App\Project $project
     * @return void
     */
    public function deleting($project)
    {
        // предварительное удаление файлов с диска
        // эти записи items потом удалятся автоматически, т.к. связаны с projects
        $items = Item::where('project_id', $project->id)->get();
        echo $project->id." - ";
        echo count($items) ." = ";
        foreach ($items as $item) {

            if ($item->base->type_is_photo() || $item->base->type_is_document()) {
                echo $item->filename() . ',';
                Storage::delete($item->filename());
            }
        }
    }

    /**
     * Handle the project "restored" event.
     *
     * @param \App\Project $project
     * @return void
     */
    public function restored($project)
    {
        //
    }

    /**
     * Handle the project "force deleted" event.
     *
     * @param \App\Project $project
     * @return void
     */
    public function forceDeleted($project)
    {
        //
    }
}
