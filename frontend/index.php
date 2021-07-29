<?php

function callAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result);
}
$config = callAPI("GET", "http://localhost:3000/api/config");
$state = callAPI("GET", "http://localhost:3000/api/state");
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title><?php echo($config->title) ?> - Sineware StatusResponse</title>
</head>
<body>
<div class="container">
    <hr />
    <h1><?php echo($config->title) ?></h1>
    <hr />
    <?php //var_dump($state); ?>
    <div class="row row-cols-1 row-cols-md-3">
        <?php
        foreach ($state as &$service) {
            if($service->up) {
                ?>
                <div class="col">
                    <div class="card text-white bg-success mb-3" style="min-height: 10rem;">
                        <div class="card-header"><?php echo($service->name); ?></div>
                        <div class="card-body">
                            <h5 class="card-title"><i><?php echo($service->url); ?></i></h5>
                            <?php echo("(HTTP " . $service->status . ": OK)"); ?>
                            <br />
                            <p class="card-text">Response Time: <?php echo($service->ping->total); ?>ms</p>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="col">
                    <div class="card text-white bg-danger mb-3" style="min-height: 10rem;">
                        <div class="card-header"><?php echo($service->name); ?></div>
                        <div class="card-body">
                            <h5 class="card-title"><i><?php echo($service->url); ?></i></h5>
                            <p class="card-text">
                                <?php
                                switch($service->status) {
                                    case 503:
                                        echo("(HTTP " . $service->status . ": Backend fetch failed)");
                                        break;
                                    case 408:
                                        echo("(HTTP " . $service->status . ": Request Timeout)");
                                        break;
                                    default:
                                        echo("(HTTP " . $service->status . ")");
                                }
                                ?>
                                <br />
                                <b>Response Time: <?php if($service->ping == null) { echo "N/A"; } else { echo($service->ping->total . "ms"); } ?></b>
                            </p>
                        </div>
                    </div>

                </div>
                <?php
            }
        }
        ?>
    </div>
    <hr />
    <p>Powered by <a href="https://github.com/Sineware/statusresponse">Sineware StatusResponse</a></p>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>
</html>
