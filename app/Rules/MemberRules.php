<?php

namespace App\Rules;

use DB;
use Exception;
use Illuminate\Contracts\Validation\Rule;

class MemberRules implements Rule
{
    private $table, $column, $type, $status;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($table, $column, $type, $status)
    {
        $this->type = $type;
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
            # Fetch the active member corresponding to UUID or ID
            $member = DB::table($this->table)
                ->where($this->column, $value)
                ->where('type', $this->type)
                ->where('status', $this->status)
                ->get();
        } catch (Exception $exception) {
            $member = false;
        }
        return $member;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :" . $this->type . " must be " . $this->status . ". ";
    }
}
