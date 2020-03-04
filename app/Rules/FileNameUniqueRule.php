<?php

namespace App\Rules;

use App\Models\ProductFile;
use Illuminate\Contracts\Validation\Rule;

class FileNameUniqueRule implements Rule
{
    private $files, $name;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($files)
    {
        $this->files = $files;
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
        $count = false;
        foreach ($this->files as $file) {
            $count = ProductFile::where('uuid', '<>', $file['uuid'])
                ->where('name', $file['name'])
                ->count();
            if ($count == 0) {
                $count = true;
            } else {
                $count      = false;
                $this->name = $file['name'];
                break;
            }
        }
        return (bool) $count;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The file name "' . $this->name . '" already exists';
    }
}
