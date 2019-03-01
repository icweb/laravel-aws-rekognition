# Laravel + AWS Rekognition Integration

This project demonstrates the integration of the AWS SDK for PHP Rekognition client into a Laravel project, covering two functions of the Rekognition service: detecting text in photos, and detecting nudity. This project is a demonstration and requires you to integrate your own use case.

[View Demo](http://laravel-aws-rekognition-demo.icwebapps.com/)
___

### Prerequisites

#### __AWS Account & Access Keys__

You will need to get your `AWS Secret Access Key` and `Access Key ID` to use the SDK. [Click here](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html?icmpid=docs_iam_console) to visit the Managing Access Keys for IAM Users page and learn how to find these keys.

#### __About Rekognition__

* [https://docs.aws.amazon.com/rekognition/latest/dg/moderation.html](https://docs.aws.amazon.com/rekognition/latest/dg/moderation.html)
* [https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectModerationLabels.html](https://docs.aws.amazon.com/rekognition/latest/dg/API_DetectModerationLabels.html)

___

### Installation

Begin by installing the AWS SDK for PHP package with Composer. Edit your `composer.json` file to require `aws/aws-sdk-php` version 3.

```json
"require": {
  "aws/aws-sdk-php": "3.*"
}
```


Then update your project by running this command in your terminal at your project root.

```
composer update
```

___

### Configuration

Add two new environment variables to your `.env` file and populate the values with your keys found in the prerequisites section.

```
AWS_SECRET_ACCESS_KEY=ENTER_YOUR_KEY
AWS_ACCESS_KEY_ID=ENTER_YOUR_KEY
```

___


### Usage

#### __Create the Client__

Create a new  `RekognitionClient` and fill in your region to begin making requests.

```php
use Aws\Rekognition\RekognitionClient;

$client = new RekognitionClient([
    'region'    => 'ENTER_YOUR_REGION',
    'version'   => 'latest'
]);
```

#### __Preparing the Image__

Convert the uploaded file into base64-encoded image bytes.

```php
$image = fopen($request->file('photo')->getPathName(), 'r');
$bytes = fread($image, $request->file('photo')->getSize());
```

#### __Create the Request__

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
