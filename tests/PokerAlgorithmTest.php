<?php
require "../vendor/autoload.php";

use skyer\TreasureChest\Algorithm\Sort\Poker;

$pokers = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A', 'Little Joker', 'Big Joker'];

try {
    var_dump(Poker::shuffle($pokers));
} catch (Exception $e) {
    echo $e->getMessage();
}