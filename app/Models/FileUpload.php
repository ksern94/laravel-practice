<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    const STATUS_PENDING    = "PENDING";
    const STATUS_PROCESSING = "PROCESSING";
    const STATUS_FAILED     = "FAILED";
    const STATUS_COMPLETED  = "COMPLETED";

    protected $table = 'file_upload';

    protected $fillable = ['file_name', 'status', 'updated_at']; 
}
