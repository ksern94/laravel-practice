<?php

namespace App\Observers;

use App\Models\FileUpload;
use App\Events\FileUploadEvent;

use Illuminate\Support\Facades\Log; 

class FileUploadObserver
{
    /**
     * Handle the FileUpload "created" event.
     *
     * @param  \App\Models\FileUpload  $fileUpload
     * @return void
     */
    public function created(FileUpload $fileUpload)
    {
        event(new FileUploadEvent($fileUpload));
    }

    /**
     * Handle the FileUpload "updated" event.
     *
     * @param  \App\Models\FileUpload  $fileUpload
     * @return void
     */
    public function updated(FileUpload $fileUpload)
    {
        event(new FileUploadEvent($fileUpload));
    }

    /**
     * Handle the FileUpload "deleted" event.
     *
     * @param  \App\Models\FileUpload  $fileUpload
     * @return void
     */
    public function deleted(FileUpload $fileUpload)
    {
        //
    }

    /**
     * Handle the FileUpload "restored" event.
     *
     * @param  \App\Models\FileUpload  $fileUpload
     * @return void
     */
    public function restored(FileUpload $fileUpload)
    {
        //
    }

    /**
     * Handle the FileUpload "force deleted" event.
     *
     * @param  \App\Models\FileUpload  $fileUpload
     * @return void
     */
    public function forceDeleted(FileUpload $fileUpload)
    {
        //
    }
}
