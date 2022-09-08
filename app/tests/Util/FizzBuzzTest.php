<?php

namespace Util;

use App\Util\FizzBuzz;
use PHPUnit\Framework\TestCase;


class FizzBuzzTest extends TestCase
{
    public function test() {
        $fizzBuzz = new FizzBuzz();

        $result = $fizzBuzz->calculate();

        $this->assertEquals($result, null);
}}