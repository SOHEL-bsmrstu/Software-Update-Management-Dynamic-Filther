<?php
namespace App;

use Exception;
use Illuminate\Support\Facades\DB;

class CallBack
{
    /**
     * @return bool
     */
    public function execute()
    {
        try {
            $db = $this->updateDataBase();
            if ($db) $execute = true;
        } catch (Exception $exception) {
            $execute = false;
        }
        return $execute ?? false;
    }

    /**
     * @return bool
     */
    public function updateDataBase()
    {
        try {
            # Execute query
            $db = DB::statement("ALTER TABLE users ADD type varchar(10)");
        } catch (Exception $exception) {
            $db = false;
        }
        return (bool) $db;
    }
}
