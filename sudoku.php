<?php

$sudoku = Sudoku::createFromFile();
include 'view.php';

class Sudoku
{
    const N = 8;

    protected $inputSudoku = [];

    protected $outputSudoku = [];

    public function __construct($sudoku)
    {
        $this->inputSudoku = $this->outputSudoku = $sudoku;
    }

    public function getOutputSudoku()
    {
        $this->calculate();
        return $this->outputSudoku;
    }

    public static function createFromFile()
    {
        $lines = file('sudoku.data');
        $sudoku = [];
        foreach ($lines as $line)
        {
            $sudoku[] = array_map('intval', explode('   ', $line));
        }
        return new static($sudoku);
    }

    protected $calculated = false;
    public function calculate()
    {
        if ($this->calculated) {
            return false;
        }
        $this->calculated = true;

        while ($this->guess()) {
            if ($this->isDone()) {
                break;
            }
        };
    }

    protected function isDone()
    {
        for ($i = 0; $i <= self::N; $i++) {
            for ($j = 0; $j <= self::N; $j++) {
                if (!$this->outputSudoku[$i][$j] > 0) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function guess()
    {
        $cells = [];
        foreach ([1,2,3,4,5,6,7,8,9] as $num) {
            for ($x = 0; $x <= self::N; $x+=3) {
                for ($y = 0; $y <= self::N; $y+=3) {

                    // 同一9宫格
                    foreach ([0,1,2] as $i) {
                        foreach ([0,1,2] as $j) {
                            if (!in_array($num, $this->getNowayNumbers($x+$i, $y+$j))) {
                                $cells["$x-$y"][$num][] = [$x + $i, $y + $j];
                            }
                        }
                    }
                    
                }
            }
        }

        foreach ([1,2,3,4,5,6,7,8,9] as $num) {
            for ($x = 0; $x <= self::N; $x += 3) {
                for ($y = 0; $y <= self::N; $y += 3) {
                    if (count($cells["$x-$y"][$num]) == 1) {
                        $pos = array_pop($cells["$x-$y"][$num]);
                        if (!$this->outputSudoku[$pos[0]][$pos[1]]) {
                            $this->outputSudoku[$pos[0]][$pos[1]] = $num;
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * 取得该单元格不可能的值
     * @param $rowCount int 单元格行数
     * @param $columnCount int 单元格列数
     * @return array()
     */
    protected function getNowayNumbers($rowCount, $columnCount)
    {
        if ($this->outputSudoku[$rowCount][$columnCount] > 0) {
            return array_diff([0,1,2,3,4,5,6,7,8,9], [$this->outputSudoku[$rowCount][$columnCount]]);
        }

        $numbers = [];
        // 同一行 或者 同一列
        foreach ($this->outputSudoku as $i => $row) {
            foreach ($row as $j => $number) {
                if ($i == $rowCount || $j == $columnCount) {
                    $numbers[] = $number;
                }
            }
        }

        // 同一9宫格
        $x = floor($rowCount / 3) * 3;
        $y = floor($columnCount / 3) * 3;
        foreach ([0,1,2] as $i) {
            foreach ([0,1,2] as $j) {
                $numbers[] = $this->outputSudoku[$x+$i][$y+$j];
            }
        }

        return array_unique($numbers);
    }

    public function render()
    {
        $this->calculate();
        ob_start();
        echo '<div class="sudoku">';
        foreach ($this->outputSudoku as $i => $row) {
            echo '<div class="row">';
            foreach ($row as $j => $number) {
                if ($number) {
                    if ($number == $this->inputSudoku[$i][$j]) {
                        echo "<span class='cell input'>$number</span>";
                    } else {
                        echo "<span class='cell'>$number</span>";
                    }
                } else {
                    echo "<span class='cell hide-number'>$number</span>";
                }
            }
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }
}
