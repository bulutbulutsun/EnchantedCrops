<?php

namespace bulutbulutsun\task;

use bulutbulutsun\Loader;
use pocketmine\scheduler\AsyncTask;

class GrowTask extends AsyncTask
{

    public function onRun(): void
    {
    }

    public function onCompletion(): void
    {
        Loader::getInstance()->getData(function (array $coordinates) {
            foreach ($coordinates as $coord) {
                Loader::getInstance()->tickCrops($coord['world'], $coord['x'], $coord['y'], $coord['z']);
            }
        });
    }
}