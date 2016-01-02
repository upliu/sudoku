<?php


/**
 * Created by PhpStorm.
 * User: liu
 * Date: 1/2/16
 * Time: 3:18 PM
 */
class Sudoku2
{
    public static $nos = [0,1,2,3,4,5,6,7,8];
    public static $numbers = [1,2,3,4,5,6,7,8,9];

    protected $sudoku;

    public function getSudoku()
    {
        return $this->sudoku;
    }
    
    public function __construct($sudoku)
    {
        foreach (static::$nos as $i) {
            foreach (static::$nos as $j) {
                $number = $sudoku[$i][$j];
                $this->sudoku['points'][$i][$j]['input'] = $number;
                $this->sudoku['points'][$i][$j]['output'] = 0;
                $this->sudoku['points'][$i][$j]['maybe'] = static::$numbers;
            }
        }
        foreach (static::$nos as $i) {
            foreach (static::$nos as $j) {
                $number = $sudoku[$i][$j];
                if ($number > 0) {
                    $this->setPostionNumber($i, $j, $number, __FUNCTION__);
                }
            }
        }
    }

    protected $calculated = false;

    public function calculate()
    {
        if ($this->calculated) {
            return false;
        }

        // 进行正常推理
        while ($this->process() || $this->guessOneNumber()) {
        }

        $this->calculated = true;
    }

    protected function guessOneNumber()
    {
        $hasGuess = false;
        foreach(static::$nos as $i) {
            foreach(static::$nos as $j) {
                if (!$this->isPositionHasNumber($i, $j)) {
                    foreach($this->sudoku['points'][$i][$j]['maybe'] as $number) {
                        $backup = $this->sudoku;
                        $this->setPostionNumber($i, $j, $number, __FUNCTION__);
                        while ($this->process()) {
                        }
                        if ($this->isDone()) {
                            return false;
                        }
                        $this->sudoku = $backup;
                        $hasGuess = true;
                    }
                }
            }
        }

        return $hasGuess;
    }

    public function isDone()
    {
        foreach(static::$nos as $i) {
            foreach(static::$nos as $j) {
                if (!$this->isPositionHasNumber($i, $j)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function isPositionHasNumber($i, $j)
    {
        return $this->sudoku['points'][$i][$j]['output'] > 0;
    }

    protected function setPostionNumber($row, $column, $number, $type)
    {
        if ($this->isPositionHasNumber($row, $column)) {
            return false;
        }

        // 同一行
        foreach (static::$nos as $j) {
            $this->sudoku['points'][$row][$j]['maybe'] = array_values(array_diff($this->sudoku['points'][$row][$j]['maybe'], [$number]));
        }
        
        // 同一列
        foreach (static::$nos as $i) {
            $this->sudoku['points'][$i][$column]['maybe'] = array_values(array_diff($this->sudoku['points'][$i][$column]['maybe'], [$number]));
        }
        
        // 同一9宫格
        $x = intval(floor($row / 3) * 3);
        $y = intval(floor($column / 3) * 3);
        foreach ([0, 1, 2] as $i) {
            foreach ([0, 1, 2] as $j) {
                $this->sudoku['points'][$x + $i][$y + $j]['maybe'] = array_values(array_diff($this->sudoku['points'][$x + $i][$y + $j]['maybe'], [$number]));
            }
        }

        $this->sudoku['points'][$row][$column]['output'] = $number;
        $this->sudoku['points'][$row][$column]['maybe'] = [];

        $this->sudoku['result'][] = [$row, $column, $number, $type];

        $this->sudoku['debug']["$row-$column"] = [
            'current' => "$row-$column: $number $type",
        ];


        return true;
    }

    public function process()
    {
        foreach (static::$nos as $i) {
            foreach (static::$nos as $j) {
                if ((!$this->isPositionHasNumber($i, $j)) && count($this->sudoku['points'][$i][$j]['maybe']) == 1) {
                    return $this->setPostionNumber($i, $j, array_pop($this->sudoku['points'][$i][$j]['maybe']), 'check_maybe');
                }
            }
        }

        $cells = [];
        $lines = [];
        $columns = [];

        foreach([0, 3, 6] as $x) {
            foreach([0, 3, 6] as $y) {
                foreach(static::$numbers as $number) {
                    foreach([0,1,2] as $i) {
                        foreach([0,1,2] as $j) {
                            $row = $x + $i;
                            $column = $y + $j;
                            if (!$this->isPositionHasNumber($row, $column) && in_array($number, $this->sudoku['points'][$row][$column]['maybe'])) {
                                $cells["$x-$y"][$number][] = [$row, $column];
                            }
                        }
                    }
                }
            }
        }
        if ($this->checkUniquePosition($cells)) {
            return true;
        }


        foreach(static::$nos as $i) {
            foreach(static::$nos as $j) {
                foreach(static::$numbers as $number) {
                    if (!$this->isPositionHasNumber($i, $j) && in_array($number, $this->sudoku['points'][$i][$j]['maybe'])) {
                        $lines[$i][$number][] = [$i, $j];
                        $columns[$j][$number][] = [$i, $j];
                    }
                }
            }
        }
        if ($this->checkUniquePosition($lines)) {
            return true;
        }
        if ($this->checkUniquePosition($columns)) {
            return true;
        }

        while ($this->checkPositionMaybeCount()) {
            if ($this->checkUniquePosition($cells) || $this->checkUniquePosition($lines) || $this->checkUniquePosition($columns)) {
                return true;
            }
        }

        if ($this->isDone()) {
            return false;
        }

        return false;
    }

    protected function checkUniquePosition($items)
    {
        foreach($items as $item) {
            foreach($item as $number => $positions) {
                if (count($positions) == 1) {
                    $pos = array_pop($positions);
                    return $this->setPostionNumber($pos[0], $pos[1], $number, __FUNCTION__);
                }
            }
        }

        return false;
    }

    protected function checkPositionMaybeCount()
    {
        $hasChange = false;

        foreach([0,3,6] as $x) {
            foreach([0,3,6] as $y) {
                $result = [];
                foreach([0,1,2] as $i){
                    foreach([0,1,2] as $j){
                        $row = $x + $i;
                        $col = $y + $j;
                        if (!$this->isPositionHasNumber($row, $col)) {
                            $result[json_encode($this->sudoku['points'][$row][$col]['maybe'])][] = [$row, $col];
                        }
                    }
                }
                foreach($result as $maybe_encode => $positions) {
                    $maybe = json_decode($maybe_encode);
                    if (count($maybe) == count($positions)) {
                        foreach([0,1,2] as $i){
                            foreach([0,1,2] as $j){
                                $row = $x + $i;
                                $col = $y + $j;
                                if (!in_array([$row, $col], $positions)) {
                                    $beforeCount = count($this->sudoku['points'][$row][$col]['maybe']);
                                    $this->sudoku['points'][$row][$col]['maybe'] = array_values(array_diff($this->sudoku['points'][$row][$col]['maybe'], $maybe));
                                    $afterCount = count($this->sudoku['points'][$row][$col]['maybe']);
                                    if ($beforeCount > $afterCount) {
                                        $hasChange = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $hasChange;
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

    public function renderQuestion()
    {
        ob_start();
        echo '<div class="sudoku">';
        foreach(static::$nos as $i) {
            echo '<div class="row">';
            foreach(static::$nos as $j) {
                $number = $this->sudoku['points'][$i][$j]['input'];
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

}