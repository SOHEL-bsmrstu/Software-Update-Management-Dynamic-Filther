<?php

namespace App\Models\License;

use App\Helpers\Traits\ListableTrait;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product as JCProduct;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class License extends Model
{
    use ListableTrait;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'license';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ["client", "order", "product"];

    public static function boot()
    {
        parent::boot();
        self::creating(function (self $model) {
            if (empty($model->key)) {
                $model->key = $model->generateKey();
            }

            if (empty($model->update_expiry)) {
                $model->update_expiry = $model->getUpdateExpiryDate();
            }

            if (empty($model->allowed_version)) {
                $model->allowed_version = $model->getAllowedVersion();
            }
        });
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function generateKey(): string
    {
        do {
            $key    = self::randomLicenseKey();
            $exists = static::where("key", $key)->withoutGlobalScopes()->exists();
        } while ($exists);

        return $key;
    }

    /**
     * @return string
     * @throws Exception
     */
    private static function randomLicenseKey(): string
    {
        $license    = null;
        $template   = str_split("XXXXX-XXXXXX-XXXXX-XXXXXX");
        $characters = str_split(self::getPermittedCharacters());

        foreach ($template as $part) {
            $license .= ($part === "-" ? "-" : $characters[array_rand($characters)]);
        }

        return $license;
    }

    /**
     * @return string
     * @throws Exception
     */
    private static function getPermittedCharacters(): string
    {
        $characters = implode(null, array(
            uniqid(),
            crc32(random_bytes(10)),
            "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"
        ));

        return str_shuffle(Str::upper($characters));
    }

    /**
     * @return Carbon
     */
    private function getUpdateExpiryDate(): Carbon
    {
        return now()->addMonths(6);
    }

    /**
     * @return string
     * @todo
     */
    private function getAllowedVersion(): string
    {
        return "1.0.0";
    }

    /**
     * @return object|array
     */
    public function getClientAttribute()
    {
        return Client::where("id", $this->client_id)->select("uuid", "email", "first_name", "last_name")->first() ?? [];
    }

    /**
     * @return object|array
     */
    public function getOrderAttribute()
    {
        return Order::where("id", $this->order_id)->select("uuid", "invoice_id")->first() ?? [];
    }

    /**
     * @return object|array
     */
    public function getProductAttribute()
    {
        try {
            $product = JCProduct::where("id", $this->getProduct()->first()->real_id)->select("id", "uuid", "name", "slug", "type")->first();

            $value = [
                "uuid"    => $product->uuid,
                "name"    => $product->name,
                "acronym" => $product->meta()->where('name', 'acronym')->select('value')->first()->value ?? [],
                "link"    => $product->type === 'digital' ? route('shop.product.show', ['slug' => $product->slug]) : route('shop.service.show', ['slug' => $product->slug]),
            ];
        } catch (Exception $exception) {
            $value = [];
        }

        return $value;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id', 'product');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'key';
    }

    /**
     * Determine whether an UUID attribute should be inserted.
     *
     * @return bool
     */
    protected function usesUuid(): bool
    {
        return false;
    }
}
