<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AppUpdate
{
    /**
     * @param string $url
     * @return bool
     */
    public static function download(string $url): bool
    {
        try {
            # Get updated file contents
            $contents = file_get_contents($url);

            # Store contents to local storage
            $download = Storage::put("updates" . DIRECTORY_SEPARATOR . "version-1.0.3.zip", $contents);
        } catch (Exception $exception) {
            $download = false;
        }

        return (bool) $download ?? false;
    }

    /**
     * @return bool
     */
    public static function remove(): bool
    {
        try {
            $remove = false;

            # Include remove files & Get the list of remove file paths
            $sourcePaths = require_once(base_path("remove.php"));

            # Remove files sequentially
            foreach ($sourcePaths["path"] as $sourcePath) {
                # Generate the remove files actual path
                $path = base_path() . DIRECTORY_SEPARATOR . $sourcePath;
                # If file or dir exists then remove it
                if (is_dir($path)) {
                    File::deleteDirectory($path);
                } else {
                    unlink($path);
                }

                $remove = true;
            }

            # After remove files delete extracted remove file
            unlink(base_path("remove.php"));
        } catch (Exception $exception) {
            $remove = false;
        }

        return (bool) $remove ?? false;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public static function extract(string $filePath): bool
    {
        try {
            $extracted = false;

            # Create ZipArchive object
            $zip = new ZipArchive();

            # Open the zip file
            $res = $zip->open($filePath);

            # If can zip open the extract files in app base path
            if ($res === true) {
                # Extract files to base path
                $zip->extractTo(base_path());

                # CLose the opened zip file
                $zip->close();
                $extracted = true;
            }

        } catch (Exception $e) {
            $extracted = false;
        }

        return (bool) $extracted ?? false;
    }

    /**
     * @return bool
     */
    public static function callback()
    {
        try {
            # Generate file path
            $filePath = app_path("CallBack.php");

            # Check file exists or not
            if (file_exists($filePath)) {
                # Include file
                require_once($filePath);

                # Check the class is exist or not
                if (class_exists("CallBack")) {
                    $call = new \App\CallBack();

                    # Check method exist or not. If exist then call
                    $execute = method_exists($call, 'execute') ? $call->execute() : false;
                }
            }
        } catch (Exception $exception) {
            $execute = false;
        }

        return $execute ?? false;
    }
}
