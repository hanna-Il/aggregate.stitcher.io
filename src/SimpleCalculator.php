<?php

namespace App\Data;

use function array_sum;

class SimpleCalculator
{
    public $prop;

    public function __construct()
    {
        $this->prop = array();
    }

    /**
     * @param int $a desc
     * @param int $b
     * @return int
     */
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function sub($a, $b)
    {
        return $a - $b;
    }

    public function sumArrayElements(ArrayProvider $arrayProvider) //some long long comment that doesn't fir the lenght of line
    {
        return array_sum($arrayProvider->getArray());
    }
}
