<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HorizonCheck implements Check
{
    public function getId()
    {
        return 'horizon';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];
        // Check if the horizon process is running
        $process = new Process(['ps', 'aux']);
        $process->run();

        if (!$process->isSuccessful()) {
            $result['message'] = 'Unable to check Horizon status';
            return $result;
        }

        $output = $process->getOutput();

        if (strpos($output, 'artisan horizon') !== false) {
            $result['success'] =  true;
            $result['message'] = 'Horizon is running';
            return $result;
        } else {
            $result['message'] = 'Horizon is not running';
            return $result;
        }


    }
}
