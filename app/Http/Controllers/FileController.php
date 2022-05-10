<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CsvProcess;
use App\Models\FileUpload;
use Carbon\Carbon;

class FileController extends Controller
{
    public function index()
    {
        $data = FileUpload::get();

        return view('home')->with(['data' => $data]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file'  => 'required|mimes:csv',
        ]);
  
        if ($file = $request->file('file')) {
             
            //store file into document folder   
            $fileName = $file->getClientOriginalName();
            $file = $request->file->storeAs('public/documents', $fileName);
            
            //save record to db
            $fileUpload = FileUpload::updateOrCreate(
                [
                    'file_name' => $fileName
                ],
                [
                    'file_name' => $fileName,
                    'status' => FileUpload::STATUS_PENDING,
                    'updated_at' => Carbon::now(),
                ]
            );

            //send job to queue
            CsvProcess::dispatch($fileName);

            return Response()->json([
                "status" => 'success',
            ]);
        }
  
        return Response()->json([
            "status" => 'error'
        ]);
    }   

}
