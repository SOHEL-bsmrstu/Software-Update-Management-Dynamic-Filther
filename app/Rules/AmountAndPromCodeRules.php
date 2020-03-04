<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AmountAndPromCodeRules implements Rule
{
    private $amount, $promo;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($amount, $promo)
    {
        $this->amount = $amount;
        $this->promo  = $promo;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->promo !== null && $this->amount !== null) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You can not send data amount & promotion field both at a time';
    }
}
