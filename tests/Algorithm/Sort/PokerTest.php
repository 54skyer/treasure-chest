<?php

namespace Test\Algorithm\Sort;

use Exception;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use TreasureChest\Algorithm\Sort\Poker;

class PokerTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [
                ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A', 'Little Joker', 'Big Joker']
            ]
        ];
    }

    /**
     * testShuffle
     *
     * @dataProvider dataProvider
     * @return void
     * @throws Exception
     */
    public function testShuffle($pokers)
    {
        $status_text = 'status_text';
        die(Str::studly($status_text));
        try {
            $this->assertIsArray(Poker::shuffle($pokers));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}