<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 1/3/16
 * Time: 2:00 AM
 */

require 'Sudoku2.php';

$sudoku = Sudoku2::createFromFile();
$sudoku->calculate();
include 'view.php';