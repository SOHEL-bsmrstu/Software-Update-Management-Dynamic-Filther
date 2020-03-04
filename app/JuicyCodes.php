<?php

namespace App;

use App\Helpers\Signer;
use App\Helpers\Types\UnencryptableType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Class JuicyCodes
 *
 * @package App
 */
final class JuicyCodes
{
    /**
     * @var Collection
     */
    public $appData;

    /** @var Signer */
    private $signer;

    /**
     * JuicyCodes constructor.
     */
    public function __construct()
    {
        $this->appData = collect([]);
    }

    /**
     * Set website URL & name
     *
     * @param string|null $url
     * @param string|null $name
     * @return void
     */
    public static function setWebsiteConfig(?string $url = null, ?string $name = null)
    {
        $url  = $url ?: setting("site_url");
        $name = $name ?: setting("site_name");

        # Set default timezone
        Config::set("app.timezone", setting("timezone") ?? "UTC");

        if (!empty($url)) {
            URL::forceRootUrl($url);
            Config::set("app.url", $url);
            Config::set("app.name", $name);
        }
    }

    /**
     * Return all the required data for Shop's js components
     *
     * @return string
     */
    public function getShopData(): string
    {
        return $this->getJSData("shop");
    }

    /**
     * Return all the required data for Client Panel's js components
     *
     * @return string
     */
    public function getClientPanelData()
    {
        return $this->getJSData("client");
    }

    /**
     * Return all the required data for Staff Panel's js components
     *
     * @return string
     */
    public function getStaffPanelData()
    {
        return $this->getJSData("staff");
    }


    public function getJSData(string $prefix)
    {
        # Get toke, routes & parameters
        $token      = csrf_token();
        $routes     = $this->getApiActionRoutes($prefix);
        $messages   = $this->getMessages($prefix);
        $parameters = $this->getRouteParameters();

        # Add various application data
        $this->appData->put("token", $token);
        $this->appData->put("routes", $routes);
        $this->appData->put("baseURL", url("/"));
        $this->appData->put("messages", $messages);
        $this->appData->put("parameters", $parameters);

        # JSON Encode all the config
        # PROD: Remove JSON pretty print
        return $this->appData->sortKeysDesc()->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Get the API & Action related routes
     *
     * @param string $prefix
     * @return array
     */
    private function getApiActionRoutes(string $prefix): array
    {
        # Get all of the routes keyed by their name
        $routes = \Route::getRoutes()->getRoutesByName();

        # Create collection of the API & Action routes URL
        # Sort the collection by the length of the route name
        $routes = collect($routes)->map(function (Route $route) {
            return $route->uri();
        })->filter(function ($uri, $name) use ($prefix) {
            return Str::is(["{$prefix}.*.api.*", "{$prefix}.*.actions.*"], $name);
        })->sortBy(function ($uri, $name) {
            return mb_strlen($name);
        });

        /** @var Collection $routes */
        return $routes->toArray();
    }

    /**
     * @param string $prefix
     * @return array
     */
    private function getMessages(string $prefix): array
    {
        return __("{$prefix}-js");
    }

    /**
     * Get raw route parameters
     *
     * @return array
     */
    private function getRouteParameters()
    {
        $parameters      = collect([]);
        $routeParameters = collect(Request::route()->parameters());

        $routeParameters->each(function ($value, $key) use ($parameters) {
            if ($value instanceof Model) {
                $column = $value->getRouteKeyName();
                $parameters->put($column, $value->{$column});
            } else {
                $parameters->put($key, $value);
            }
        });

        return $parameters->toArray();
    }

    /**
     * Get signed route URL
     *
     * @param string $name
     * @param mixed  ...$params
     * @return string
     */
    public function signedRoute(string $name, ...$params): string
    {
        #  Convert provided params to string
        $routeParams = implode("::", $params);


        # Encrypt provided parameters
        $params = array_map(function ($value) {
            return ($value instanceof UnencryptableType
                ? (string) $value
                : $this->signer()->encrypt($value));
        }, $params);

        # Add encrypted signature to params list
        $params["signature"] = $this->signer()->sign($routeParams, $name);

        # Generate the URL to a named route
        return route($name, $params);
    }

    /**
     * Get Signer instance
     *
     * @return Signer
     */
    public function signer(): Signer
    {
        # Create new Cipher instance if there isn't one
        if ($this->signer instanceof Signer === false) {
            $this->signer = new Signer(
                ';gsG:]' . 'R-KP83' . ';S*xwg' . 'X6X8:o' . '0Pz5.jIK'
            );
        }

        return $this->signer;
    }

    /**
     * Add data for JS usage
     *
     * @param $data
     * @return $this
     */
    public function data($data)
    {
        # Add data for JS usage
        $this->appData->put("data", $data);

        return $this;
    }
}
