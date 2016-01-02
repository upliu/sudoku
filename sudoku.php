<?php

$sudoku = Sudoku::createFromFile();
include 'view.php';

class Sudoku
{
    const N = 8;

    protected $inputSudoku = [];

    protected $outputSudoku = [];

    public $result = [];

    public $cells = [];

    public $points = [];

    public $debug = [];

    public function __construct($sudoku)
    {
        $this->inputSudoku = $this->outputSudoku = $sudoku;
        $this->init();
        $this->calculate();
    }

    public function getOutputSudoku()
    {
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
        return $this->calculatePoints() || $this->calculateCells() || $this->combileCell();
    }

    /**
     * 初始化单元格可能取值
     * @return array()
     */
    protected function init()
    {
        for ($i = 0; $i <= self::N; $i++) {
            for ($j = 0; $j <= self::N; $j++) {
                $this->points[$i][$j] = [
                    'value' => 0,
                    'maybe' => [1, 2, 3, 4, 5, 6, 7, 8, 9],
                ];
            }
        }
    }

    protected function calculateCells()
    {
        for ($x = 0; $x <= self::N; $x += 3) {
            for ($y = 0; $y <= self::N; $y += 3) {
                foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num) {
                    $this->cells["$x-$y"][$num] = [];
                    // 同一9宫格
                    foreach ([0, 1, 2] as $i) {
                        foreach ([0, 1, 2] as $j) {
                            if (!$this->isPositionHasNumber($x + $i, $y + $j) && in_array($num, $this->points[$x + $i][$y + $j]['maybe'])) {
                                $this->cells["$x-$y"][$num][] = [$x + $i, $y + $j];
                            }
                        }
                    }

                }
            }
        }

        return $this->checkCells();
    }

    public function checkCells()
    {
        foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $number) {
            for ($x = 0; $x <= self::N; $x += 3) {
                for ($y = 0; $y <= self::N; $y += 3) {
                    if (count($this->cells["$x-$y"][$number]) == 1) {
                        $pos = $this->cells["$x-$y"][$number][0];
                        if ($this->setPostionNumber($pos[0], $pos[1], $number, __FUNCTION__)) {
                            return true;
                        }
                    }

                }
            }
        }

        return false;
    }
    
    protected function calculatePoints() {
        for ($i = 0; $i <= self::N; $i++) {
            for ($j = 0; $j <= self::N; $j++) {
                if ($number = $this->outputSudoku[$i][$j]) {

                }
            }
        }

        return $this->checkPoints();
    }

    protected function combileCell()
    {
        $hasChange = false;
        foreach ($this->cells as $k => $cell) {
            $grp = [];
            foreach ($cell as $number => $positions) {
                if ($this->checkNumberPositions([$number], $positions)) {
                    $hasChange = true;
                }
                if (count($positions) > 0) {
                    $grp[json_encode($positions)][] = $number;
                }
            }
            foreach ($grp as $identify => $numbers) {
                $positions = json_decode($identify, true);
                if (count($numbers) == count($positions)) {
                    if ($this->checkNumberPositions($numbers, $positions)) {
                        $hasChange = true;
                    }
                }
            }
        }

        return $hasChange;
    }
    
    protected function checkNumberPositions($numbers, $positions)
    {
        if (count($positions) < 2) {
            return false;
        }
        $hasChange = false;
        if (($row = $this->getRowNo($positions)) !== false) {
            for ($n = 0; $n <= self::N; $n++) {
                $this->points[$row][$n]['maybe'] = array_values(array_diff($this->points[$row][$n]['maybe'], $numbers));
            }
            $hasChange = true;
        } else if (($col = $this->getColumnNo($positions)) !== false) {
            for ($n = 0; $n <= self::N; $n++) {
                $this->points[$n][$col]['maybe'] = array_values(array_diff($this->points[$n][$col]['maybe'], $numbers));
            }
            $hasChange = true;
        }
        return $hasChange;
    }

    protected function getRowNo($positions)
    {
        $p = array_pop($positions);
        $row = $p[0];
        foreach ($positions as $p) {
            if ($row != $p[0]) {
                return false;
            }
        }

        return $row;
    }

    protected function getColumnNo($positions)
    {
        $p = array_pop($positions);
        $col = $p[1];
        foreach ($positions as $p) {
            if ($col != $p[1]) {
                return false;
            }
        }
        return $col;
    }

    protected function checkPoints()
    {
        for ($i = 0; $i <= self::N; $i++) {
            for ($j = 0; $j <= self::N; $j++) {
                if (count($this->points[$i][$j]['maybe']) == 1) {
                    $number = $this->points[$i][$j]['maybe'][0];
                    if ($this->setPostionNumber($i, $j, $number, __FUNCTION__)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function isPositionHasNumber($i, $j)
    {
        return $this->outputSudoku[$i][$j] > 0;
    }

    protected function setPostionNumber($i, $j, $number, $func = 'init')
    {
        if ($this->isPositionHasNumber($i, $j)) {
            return false;
        }

        $this->debug["$i-$j"] = [
            'current' => "$i-$j: $number $func",
            'points' => $this->points,
            'cells' => $this->cells];

        $this->points[$i][$j]['value'] = $number;
        $this->outputSudoku[$i][$j] = $number;
        $this->result[] = [$i, $j, $number];

        // 同一行 或者 同一列
        for ($ii = 0; $ii <= self::N; $ii++) {
            for ($jj = 0; $jj <= self::N; $jj++) {
                if ($ii == $i || $jj == $j) {
                    $this->points[$ii][$jj]['maybe'] = array_values(array_diff($this->points[$ii][$jj]['maybe'], [$number]));
                }
            }
        }

        // 同一9宫格
        $x = intval(floor($i / 3) * 3);
        $y = intval(floor($j / 3) * 3);
        foreach ([0, 1, 2] as $ii) {
            foreach ([0, 1, 2] as $jj) {
                $this->points[$x + $ii][$y + $jj]['maybe'] = array_values(array_diff($this->points[$x + $ii][$y + $jj]['maybe'], [$number]));
            }
        }

        $this->points[$i][$j]['maybe'] = [];

        return true;
    }

    public function renderQuestion()
    {
        ob_start();
        echo '<div class="sudoku">';
        for ($i = 0; $i <= self::N; $i++) {
            echo '<div class="row">';
            for ($j = 0; $j <= self::N; $j++) {
                $number = $this->inputSudoku[$i][$j];
                if ($number) {
                    echo "<span class='cell input'>$number</span>";
                } else {
                    echo "<span class='cell'>&nbsp;</span>";
                }
            }
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public function renderAnswer()
    {
        ob_start();
        echo '<div class="sudoku">';
        for ($i = 0; $i <= self::N; $i++) {
            echo '<div class="row">';
            for ($j = 0; $j <= self::N; $j++) {
                $number = $this->outputSudoku[$i][$j];
                if ($number == $this->inputSudoku[$i][$j]) {
                    echo "<span class='cell input'>$number</span>";
                } else {
                    echo "<span class='cell'>$number</span>";
                }
            }
            echo '</div>';
        }
        echo '</div>';
        return ob_get_clean();
    }
}
