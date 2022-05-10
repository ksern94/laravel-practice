<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log; 
use App\Models\FileUpload;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use DB;

class CsvProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fileName;
    private static $CSV_HEADER = [
        "UNIQUE_KEY",
        "PRODUCT_TITLE",
        "PRODUCT_DESCRIPTION",
        "STYLE#",
        "SANMAR_MAINFRAME_COLOR",
        "SIZE",
        "COLOR_NAME",
        "PIECE_PRICE",
    ];

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 180;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->updateFileStatus(FileUpload::STATUS_PROCESSING);

        $filePath = storage_path('app/public/documents/'.$this->fileName);

        $data = $this->loadCSV($filePath);

        //parsing requirement, check header
        $csv_header = array_keys($data[0]);
        $check_header = self::$CSV_HEADER;

        // Log::info($csv_header);
        // Log::info($check_header);
        if(array_diff($check_header, $csv_header))
        {
            Log::info("failed header check");
            //doesn't contains all value
            return $this->updateFileStatus(FileUpload::STATUS_FAILED);
        }

        //saving
        DB::beginTransaction();
        try {
            foreach($data as $oneData)
            {   
                //set empty string to null
                foreach ($oneData as $i => $value) {
                    if ($value === "") $oneData[$i] = null;
                }

                // Log::info($oneData['UNIQUE_KEY']);
                $oneData['updated_at'] = Carbon::now();

                Product::updateOrCreate(
                    [
                        'UNIQUE_KEY' => $oneData['UNIQUE_KEY']
                    ],
                    $oneData
                );

                // throw new Exception('Simulate an exception');
            }
        }
        catch(Exception $e) {
            Log::info('rollback');
            Log::info($e->getMessage());
            DB::rollBack();
            return $this->updateFileStatus(FileUpload::STATUS_FAILED);
        }
        DB::commit();

        return $this->updateFileStatus(FileUpload::STATUS_COMPLETED);
    }

    private function updateFileStatus($fileStatus)
    {
        Log::info($fileStatus);
        //update
        $fileUpload = FileUpload::where('file_name', $this->fileName)->first();

        $fileUpload->update([
            'status' => $fileStatus,
            'updated_at' => Carbon::now(),
        ]);
    }

    private function loadCSV($file)
    {
        // Create an array to hold the data
        $arrData = array();
        
        // Create a variable to hold the header information
        $header = NULL;
        
        // If the file can be opened as readable, bind a named resource
        if (($handle = fopen($file, 'r')) !== FALSE)
        {
            // Loop through each row
            while (($row = fgetcsv($handle)) !== FALSE)
            {
                // Loop through each field
                foreach($row as &$field)
                {
                    // Remove any invalid or hidden characters
                    $field = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                }
                
                // If the header has been stored
                if ($header)
                {
                    // Create an associative array with the data
                    $arrData[] = array_combine($header, $row);
                }
                else
                {
                    // Store the current row as the header
                    $header = $row;
                }
            }
            
            // Close the file pointer
            fclose($handle);
        }
        
        return $arrData;
    }
}
