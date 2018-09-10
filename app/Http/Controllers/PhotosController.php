<?php

namespace App\Http\Controllers;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;

class PhotosController extends Controller
{
    public function showForm()
    {
        return view('form');
    }

    public function submitForm(Request $request)
    {
        $client = new RekognitionClient([
            'region'    => 'us-west-2',
            'version'   => 'latest'
        ]);

        $image = fopen($request->file('photo')->getPathName(), 'r');
        $bytes = fread($image, $request->file('photo')->getSize());

        if($request->input('type') === 'nudity')
        {
            $results = $client->detectModerationLabels(['Image' => ['Bytes' => $bytes], 'MinConfidence' => intval($request->input('confidence'))])['ModerationLabels'];

            if(array_search('Explicit Nudity', array_column($results, 'Name')))
            {
                $message = 'This photo may contain nudity';
            }
            else
            {
                $message = 'This photo does not contain nudity';
            }
        }
        else
        {
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
        }

        request()->session()->flash('success', $message);

        return view('form', ['results' => $results]);
    }
}
