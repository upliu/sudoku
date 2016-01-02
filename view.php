<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sudoku</title>
    <style>
        .sudoku {
            display: none;
            border: 2px solid black;
        }
        <?php $s = 60; ?>
        .cell {
            display: inline-block;
            width: <?= $s ?>px;
            height: <?= $s ?>px;
            line-height: <?= $s ?>px;
            text-align: center;
            font-size: 45px;
            color: grey;
        }
        .input {
            font-weight: bolder;
            color: black;
        }
    </style>
</head>
<body>
<?= $sudoku->renderQuestion() ?>
<script src="/jquery.min.js"></script>
<script>
    var SUDOKU = <?= json_encode($sudoku->getSudoku()) ?>;
    var RESULT = SUDOKU.result;
    var POINTS = SUDOKU.points;
    var DEBUG = SUDOKU.debug;
    var border = '1px solid black';
    var borderStrong = '2px solid black';
    [2,5].forEach(function(i){
        $('.row').get(i).style.borderBottom = borderStrong;
    });
    [0,1,3,4,6,7].forEach(function(i){
        $('.row').get(i).style.borderBottom = border;
    });
    $('.row').each(function(){
        var $row = $(this);
        [2,5].forEach(function(i){
            $row.find('.cell').get(i).style.borderRight = borderStrong;
        });
        [0,1,3,4,6,7].forEach(function(i){
            $row.find('.cell').get(i).style.borderRight = border;
        });
    });
    $('.sudoku').css('display', 'inline-block');

    $('.cell').click(function(){
        var i = $(this).closest('.row').index();
        var j = $(this).index();
        var point = POINTS[i][j];
        if (point.output) {
            console.log(i+'-'+j+':', DEBUG[i + '-' + j])
        } else {
            console.log(i+'-'+j+':', point.maybe.join(' '));
        }
    });

    (function(){
        var idx = 0;
        var timer = 50;
        var result = [];
        RESULT.forEach(function(v){
            if (v[3] != '__construct') {
                result.push(v);
            }
        });
        function writeResult()
        {
            if (idx >= result.length) {
                return;
            }

            var x = result[idx][0];
            var y = result[idx][1];
            var number = result[idx][2];
            var $cell = $($($('.row')[x]).find('.cell').get(y));
            $cell.text(number);
            setTimeout(writeResult, timer);

            idx++;
        }
        writeResult();
    })();

</script>
</body>
</html>