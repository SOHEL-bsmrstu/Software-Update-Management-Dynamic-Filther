<?php

namespace App\Rules\Staff;

use App\Models\License\License;
use Illuminate\Contracts\Validation\Rule;

class LicenseUniqueKey implements Rule
{
    private $licenseId = null;

    /**
     * Create a new rule instance.
     *
     * @param string|null $id
     */
    public function __construct(string $id = null)
    {
        $this->licenseId = $id;
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
        try {
            # Check if key exists or not
            $exists = License::where('code', $value)->where('id', '<>', $this->licenseId)->exists();
        } catch (\Exception $exception) {
            $exists = false;
        }

        return (bool) !$exists;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The license key is already taken. please enter a unique license key.";
    }
}
