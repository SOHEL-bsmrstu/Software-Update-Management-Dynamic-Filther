<?php

namespace App\Rules;

use DB;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class PaymentGatewayStatusCheckRules implements Rule
{
    private $table, $column, $status;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($table, $column, $status)
    {
        $this->table = $table;
        $this->column = $column;
        $this->status = $status;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            # Fetch payment gateway corresponding to column & status
            $gateway = DB::table($this->table)
                ->where($this->column, $value)
                ->where('status', $this->status)
                ->first();

        } catch (Exception $exception) {
            $gateway = false;
        }

        return $gateway;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :payment_gateway must be " . $this->status . ".";
    }
}
