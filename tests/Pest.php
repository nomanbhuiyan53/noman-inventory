<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Unit tests: uses the base Pest TestCase (no Laravel bootstrap)
| Feature tests: uses Noman\Inventory\Tests\TestCase (Orchestra Testbench)
|
*/

uses(\Noman\Inventory\Tests\TestCase::class)
    ->in('Feature', 'Integration');

uses(\PHPUnit\Framework\TestCase::class)
    ->in('Unit');
