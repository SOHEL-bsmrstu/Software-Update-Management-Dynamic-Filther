<?php

namespace App\Http\Controllers;

use App\JuicyCodes;
use GuzzleHttp\Client;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * @return Factory|View
     */
    public function home(){
        $client = new Client();

        $response = $client->request("GET", "http://juicycodes.public/api/updates/pdf-convert/license_uuid/1.3.0");
        $updates  = $response !== null ? json_decode($response->getBody())->updates[0] : null;

        return view('welcome', compact("updates"));
    }
}
