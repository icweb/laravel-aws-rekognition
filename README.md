# Laravel + AWS Rekognition Integration

This tutorial demonstrates the integration of the AWS SDK for PHP Rekognition client into a Laravel project, covering two functions of the Rekognition service: detecting text in photos, and detecting nudity.
___

### Table of Contents

- [Demo](#Demo)
- [Prerequisites](#Prerequisites)
- [Tutorial](#Tutorial)
    - [1. Create Laravel Project](#CreateLaravelProject)
    - [2. Setup Laravel Project](#SetupLaravelProject)
    - [3. Create Controller](#CreateController)
    - [4. Create Routes](#CreateRoutes)
    - [5. Create View](#CreateView)
    - [6. Create Rekognition Client](#CreateClient)
    - [7. Prepare Uploaded Image](#PrepareImage)
    - [7. Add Logic](#AddLogic)
- [Request Examples](#RequestExamples)
___

<a name="Demo">

### Demo

A demo of the completed tutorial is available below.

__Demo URL:__ [http://laravel-aws-rekognition-demo.icwebapps.com/](http://laravel-aws-rekognition-demo.icwebapps.com/)
___

<a name="Prerequisites">

### Prerequisites

#### __AWS Account & Access Keys__

You will need to get your `AWS Secret Access Key` and `Access Key ID` to use the SDK. [Click here](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html?icmpid=docs_iam_console) to visit the Managing Access Keys for IAM Users page and learn how to find these keys.

#### __About Rekognition__

* [https://docs.aws.amazon.com/rekognition/latest/dg/moderation.html](https://docs.aws.amazon.com/rekognition/latest/dg/moderation.html)
* [https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectModerationLabels.html](https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectModerationLabels.html)

___

<a name="Tutorial">

### Tutorial

<a name="CreateLaravelProject">

#### 1. Create Laravel Project

To begin, let's install an empty Laravel project. For this tutorial, we will use the global Laravel installer. You can [click here](https://laravel.com/docs/5.8/installation) to learn more about the global installer.
```
laravel new rekognition
```

Change into the project directory
```
cd rekognition
```

<a name="SetupLaravelProject">

#### 2. Setup Laravel Project

Add the AWS SDK for PHP package to your `composer.json` file to require `aws/aws-sdk-php` version 3.

```json
"require": {
  "aws/aws-sdk-php": "3.*"
}
```

Install the project dependencies, this will also install the AWS SDK for PHP
```
composer install
```

Create an application environment file
```
mv .env.example .env
```

Add two new environment variables to your `.env` file and populate the values with your keys found in the prerequisites section.

```
AWS_SECRET_ACCESS_KEY=ENTER_YOUR_KEY
AWS_ACCESS_KEY_ID=ENTER_YOUR_KEY
```

Generate the application key
```
php artisan key:generate
```

Set directory permissions
```
sudo chmod -R 777 bootstrap
sudo chmod -R 777 storage
```

<a name="CreateController">

#### 3. Create Controller

Create a new controller called `PhotosController` by running the command below. This is where we will put all the logic.

```
php artisan make:controller PhotosController
```

Open the new controller and add two new methods, one to show the form, and one to receive the form submission.

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhotosController extends Controller
{
    public function showForm()
    {
        return view('form');
    }
    
    public function submitForm(Request $request)
    {
        //
    }
}
```

In the `showForm` method, we will simply return a view we'll create later. In the `submitForm` method, all we'll do for now is include the `Request $request` parameter, and add `Use Illuminate\Http\Request` at the top.

<a name="CreateRoutes">

#### 4. Create Routes

Open the `web.php` file in the `routes` directory and create two new routes

```php
Route::get('/', 'PhotosController@showForm');
Route::post('/', 'PhotosController@submitForm');
```


<a name="CreateView">

#### 5. Create View

Create a new blade file in the following location:
```
/resources/views/form.blade.php
```

Add the following contents to the `form.blade.php` file:
```blade
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel + AWS Rekognition</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>

<div class="container">

    <div class="jumbotron">
        <h3>Laravel + AWS Rekognition SDK Integration</h3>
        <p>This project demonstrates the integration of the AWS Rekognition SDK into a Laravel project.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <div class="form-group">{{ session('success') }}</div>
            <a href="/" class="btn btn-success">Try Again</a>
        </div>
    @endif

    @if(isset($results))
        {{ dd($results) }}
    @else
        <form action="{{ action('PhotosController@submitForm') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="type">Action</label>
                <select name="type" id="type" class="form-control">
                    <option value="text">Read Text</option>
                    <option value="nudity">Detect Nudity</option>
                </select>
            </div>
            <div class="form-group">
                <label for="confidence">Minimum Confidence</label>
                <input type="number" id="confidence" name="confidence" class="form-control" value="50">
            </div>
            <div class="form-group">
                <label for="photo">Upload a Photo</label>
                <input type="file" name="photo" id="photo" class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" value="Submit" class="btn btn-success btn-lg">
            </div>
        </form>
    @endif

</div>

</body>
</html>
```

<a name="CreateClient">

#### 6. Create Rekognition Client

Inside the `submitForm` method of the `PhotosController` controller, add a line to create the Rekognition client. Be sure to add `use Aws\Rekognition\RekognitionClient;` at the top of your file


```php
use Illuminate\Http\Request;

class PhotosController extends Controller
{  
    public function submitForm(Request $request)
    {
        $client = new RekognitionClient([
            'region'    => 'ENTER_YOUR_REGION',
            'version'   => 'latest'
        ]);
    }
}
```

<a name="PrepareImage">

#### 7. Prepare Uploaded Image

Convert the uploaded file into base64-encoded image bytes. In the snippet below, we are looking for a `FILE` field with the name `photo`.

```php
public function submitForm(Request $request)
{
    $client = new RekognitionClient([
        'region'    => 'ENTER_YOUR_REGION',
        'version'   => 'latest'
    ]);
    
    $image = fopen($request->file('photo')->getPathName(), 'r');
    $bytes = fread($image, $request->file('photo')->getSize());
}
```

<a name="AddLogic">

#### 8. Add Logic

Open the `PhotosController` file and add the following lines to the `submitForm` method:

```php
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
```

<a name="RequestExamples">

#### __Request Examples__

Create a request to Rekognition. Supply the image bytes, and enter a minimum confidence level for your labels.

__Detect Nudity__

```php
$results = $client->detectModerationLabels([
    'Image'         => ['Bytes' => $bytes],
    'MinConfidence' => 50
])['ModerationLabels'];

# Check to see if nudity labels were returned
$containsNudity = array_search('Explicit Nudity', array_column($results, 'Name'));
```


 __Detect Text in Photo__

 ```php
 $results = $client->detectText([
     'Image'         => ['Bytes' => $bytes],
 ])['TextDetections'];

# Create single string of all words detected
foreach($results as $item)
{
    if($item['Type'] === 'WORD')
    {
        $string .= $item['DetectedText'] . ' ';
    }
}
 ```
