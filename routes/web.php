<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', "HomeController@home")->name("home");
Route::put("/update", "AppUpdateController@update")->name("app.update");

Route::get("/filter", function () {

    $request = new Illuminate\Http\Request();
    $request->replace([
        "type"           => ["admin"],
        "text"           => "Balistreri",
        "startDate"      => "2020-02-29",
        "endDate"        => "2020-03-29",
        "exclude_status" => ["unapproved"],
        "status"         => ["active", "block"],
        "exclude_type"   => ["client", "editor"],
    ]);

    $filter = new \App\Helpers\Filter(new \App\Models\Member(), $request);

    $filter->dateFilter();
    $filter->includeFilter("status", "type");
    $filter->excludeFilter("status", "type");
    $filter->textFilter("first_name", "last_name", "company_name", "email");

    dd($filter->paginate());

})->name("filter");
