<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class ClientDateLength implements Rule
{
    private $start = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($start)
    {
        $this->start = $start;
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
        if ($this->start === null && $value === null) {
            return true;
        } else {
            $startDate = Carbon::createFromDate($this->start);
            $endDate   = Carbon::createFromDate($value);
            $dayLength = $startDate->diffInDays($endDate);

            return $dayLength <= 31 ? true : false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Difference between two dates can not extends "Thirty One(31)"';
    }
}
