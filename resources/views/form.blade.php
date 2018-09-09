<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel + AWS Rekognition</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
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