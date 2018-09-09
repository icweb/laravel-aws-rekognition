<?php

namespace App\Http\Controllers;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhotosController extends Controller
{
    public function showForm()
    {
        return view('form');
    }

    public function submitForm(Request $request)
    {
        $client = new RekognitionClient([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ],
            'region'    => 'us-west-2',
            'version'   => 'latest'
        ]);

        $image = fopen($request->file('photo')->getPathName(), 'r');
        $bytes = fread($image, $request->file('photo')->getSize());

        if($request->input('type') === 'nudity')
        {
            // https://docs.aws.amazon.com/rekognition/latest/dg/moderation.html
            // https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectModerationLabels.html

            $results = $client->detectModerationLabels(['Image' => ['Bytes' => $bytes], 'MinConfidence' => intval($request->input('confidence'))])['ModerationLabels'];

            if(array_search('Explicit Nudity', array_column($results, 'Name')))
            {
                $message = 'This photo may contain nudity';
            }
            else
            {
                $message = 'This photo does not contain nudity';
            }

            DB::table('upload_logs')->insert(['type' => 'nudity', 'results' => count($results), 'created_at' => date('Y-m-d H:i:s')]);
        }
        else
        {
            // https://docs.aws.amazon.com/rekognition/latest/dg/text-detection.html
            // https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectText.html

            $results = $client->detectText(['Image' => ['Bytes' => $bytes], 'MinConfidence' => intval($request->input('confidence'))])['TextDetections'];

            $string = '';
            foreach($results as $item)
            {
                if($item['Type'] === 'WORD')
                {
                    $string .= $item['DetectedText'] . ' ';
                }
            }

            if(empty($string))
            {
                $message = 'This photo does not have any words';
            }
            else
            {
                $message = 'This photo says ' . $string;
            }

            DB::table('upload_logs')->insert(['type' => 'text_read', 'results' => count($results), 'created_at' => date('Y-m-d H:i:s')]);
        }

        request()->session()->flash('success', $message);

        return view('form', ['results' => $results]);
    }
}
