<?php
use phpDocumentor\Reflection\Types\Null_;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/db', function(){
    Cache::flush();
    echo app()->environment();
});

$router->get('/test', function(){
    $plate1 = '13';
    $plate2 = '1203';
    $url = 'http://gpstmc.dlt.go.th/dltgps/web/map_mobile/track_api.php?plate1=' . $plate1 . '&plate2=' . $plate2 . '&off_code=1&method=check_plate';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $data = json_decode($result, true);
        $uID = $data[0]["unit_id"];
        $url = 'http://gpstmc.dlt.go.th/dltgps/web/map_mobile/track_api.php?id=' . $uID . '&tracking_type=0&token=&method=location';
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Execute
        $result = curl_exec($ch);
        curl_close($ch);
        $trackInfo = json_decode($result, true);
        if($trackInfo[1] != null){
            return end($trackInfo[1]);
        }
        else{
            return Null;
        }

});

$router->get('/track/{busID}', function ($busID) use ($router) {
    header('Access-Control-Allow-Origin: *');
    function trackBus($plate1, $plate2)
    {
        $url = 'http://gpstmc.dlt.go.th/dltgps/web/map_mobile/track_api.php?plate1=' . $plate1 . '&plate2=' . $plate2 . '&off_code=1&method=check_plate';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $data = json_decode($result, true);
        $uID = $data[0]["unit_id"];
        $url = 'http://gpstmc.dlt.go.th/dltgps/web/map_mobile/track_api.php?id=' . $uID . '&tracking_type=0&token=&method=location';
        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);
        // Execute
        $result = curl_exec($ch);
        curl_close($ch);
        $trackInfo = json_decode($result, true);
        //$latestLocation = end($trackInfo[1]);
        //return var_dump($latestLocation);
        //return $latestLocation;
        if($trackInfo[1] != null){
            return end($trackInfo[1]);
        }
        else{
            return Null;
        }
    }
    $db = DB::table('Bus')
        ->where('busID', $busID)
        ->first();
    $busPlateJ = json_decode($db->busPlate, true);
    $location = array();
    foreach ($busPlateJ as $plate => $plateV) {
        $k = explode("-", $plateV);
        array_push($location, trackBus($k[0], $k[1]));
    }
    // return $latestLocation;
    $location = array_values(array_filter($location));
    return $location;
});
