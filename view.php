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
            font-size: 30px;
        }
        .input {
            font-weight: bolder;
            color: red;
        }
        .hide-number {
            text-indent: -9999px;
        }
    </style>
</head>
<body>
<?= $sudoku->render() ?>
<script src="/jquery.min.js"></script>
<script>
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
</script>
</body>
</html>