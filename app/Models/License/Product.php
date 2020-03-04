<?php

namespace App\Models\License;

use App\Models\Product as JCProduct;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'license';

    protected $fillable = ['*'];

    /**
     * @param string $uuid
     * @return int
     */
    public static function getIdByRealUuid(string $uuid): int
    {
        $realId = JCProduct::whereUuid($uuid)->select("id")->first()->id;

        return self::where("real_id", $realId)->select("id")->first()->id;
    }
}
