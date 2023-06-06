<?php

namespace App\Plugins\SystemChecks;
use Illuminate\Support\Arr;


class SystemChecksManager
{
    public static function runAllChecksForAPI()
    {
        $checks = self::getChecks();

        $results = [];

        foreach ($checks as $check) {
            $results[$check->getId()] = $check->run();
        }

        return $results;
    }



    public static function runAllChecksForUI()
    {
        $checks = self::getChecks();

        $results = [];

        foreach ($checks as $key => $check) {
            $data =  $check->run();
            $data['running'] = (isset($data['success']) && $data['success']) ? '<img src="/images/icon-checkmark.svg" alt="Success">' : '<img src="/images/icon-error.svg" alt="Error">';

            $results[$key] =  Arr::add($data,'name',$check->getId());
        }

        return $results;
    }

    public static function getChecks()
    {
        // Scan the plugins directory for files that implement the Check interface
        $checks = [];

        $files = scandir(__DIR__ . '/Checks');
        foreach ($files as $file) {
            if (is_file(__DIR__ . '/Checks/' . $file)) {
                $className = 'App\\Plugins\\SystemChecks\\Checks\\' . str_replace('.php', '', $file);
                $reflection = new \ReflectionClass($className);
                if ($reflection->implementsInterface('App\\Plugins\\SystemChecks\\Check')) {
                    $checks[] = new $className();
                }
            }
        }

        return $checks;
    }
}
