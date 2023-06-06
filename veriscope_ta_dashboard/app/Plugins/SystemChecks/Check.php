<?php

namespace App\Plugins\SystemChecks;

interface Check
{
    public function getId();

    public function run();
}
