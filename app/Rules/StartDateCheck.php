<?php

namespace App\Rules;

//use Carbon\Carbon;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class StartDateCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $start_date_time = strtotime($value);
        $start_date      = date('Y-m-d', $start_date_time);

        if ($start_date > Carbon::yesterday()) {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
