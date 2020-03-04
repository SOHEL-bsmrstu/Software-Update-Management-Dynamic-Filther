<?php

namespace App\Http\Controllers;

use App\Helpers\AppUpdate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AppUpdateController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request){
        $remove = false;
        $execute = false;
        $update = false;

        try {
            # Download latest updated files
            $download = AppUpdate::download("http://pdf-manipulation.public/app.zip");

            # Download stored file path
            $path = "app". DIRECTORY_SEPARATOR ."updates";
            $storagePath = storage_path( $path . DIRECTORY_SEPARATOR . "version-1.0.3.zip");

            if ($download){
                # Extract downloaded updated files into app root dir
                $extract = AppUpdate::extract($storagePath);

                if ($extract) {
                    # Remove unused files
                    $remove = AppUpdate::remove();

                    # Callback after remove files
                    $execute = AppUpdate::callback();
                }

                # Set update status
                $update = $extract && $remove && $execute;

                # Remove created dir
                File::deleteDirectory($path);            }
        }catch (Exception $exception){
            $update = false;
        }

        return response()->json(["success" => (bool) $update]);
    }
}
