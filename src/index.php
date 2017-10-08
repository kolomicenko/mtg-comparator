<?php

require_once __DIR__ . '/core.php';

use MTG_Comparator\DB;

// TODO hard-set price limit (for cards downloading) - e.g., do not bother with cards cheaper than $2
// TODO rytir: buy = 70% of sell, valid for all cards?

$default_rate = 22.05;
$default_limit = 0;

if (!isset($_GET['rate']) or !isset($_GET['limit'])) {
    header("location: ?rate=" . $default_rate . "&limit=" . $default_limit);
    exit;
}


?>

<html>
<head>

<style type="text/css">

label {
    padding-left: 10px;
    padding-right: 10px;
}

form > input {
    width: 50px;
}

.left {
    float: left;
}

.right {
    float: right;
}

a {
    color: blue;
}

/*
tr:first-child {
  font-weight: bold;
}

td {
  vertical-align: inherit;
  text-align: center;
  padding: 7px;
}

td:first-child {
  font-weight: bold;
  text-align: left;
}
*/

</style>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.css">

</head>

<body>

<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>

<div class="left">
<form method="get">
    <label for="currency_input">USD/CZK currency rate:</label><input type="text" id="currency_input" value="<?php echo $_GET['rate']; ?>" name="rate">
    <label for="profit_limit_input">Show only cards with profit above:</label><input type="text" id="profit_limit_input" value="<?php echo $_GET['limit']; ?>" name="limit"><label for="profit_limit_input">USD</label>
    <input type="submit" value="Go">
</form>
</div>

<?php

// number of non-matched editions for the div on top-right
$nonmatched_editions_cnt = DB::query('select min(cnt) as nonmatched from (select count(*) as cnt from edition e join shop s on e.shop_id = s.id where not exists (select * from editions_pair where edition1 = e.id) group by s.id) as a')->fetchColumn();


DB::query("SET @rate_usd_kc := ?", $_GET['rate']);
DB::query("SET @profit_limit := ?", $_GET['limit']);

$result = DB::query("select a.name, a.is_foil, a.quality, a.language, a.edition_name, a.sell_id, a.buy_id, round((a.buy_price / @rate_usd_kc - a.sell_price) * a.pieces, 2) as profit, round(a.sell_price * a.pieces, 2) as purchase_total, round((a.buy_price / @rate_usd_kc - a.sell_price) * a.pieces / (a.sell_price * a.pieces), 2) as profit_to_purchase, a.pieces
from
(select card_sell.name as name, if(card_sell.is_foil, 'foil', 'non-foil') as is_foil, card_sell.quality as quality, card_sell.language as language, e.name as edition_name, card_sell.id as sell_id, card_buy.id buy_id, card_buy.price as buy_price, card_sell.price as sell_price, least(card_buy.pieces, card_sell.pieces) as pieces
from card card_sell inner join card card_buy on card_sell.name = card_buy.name inner join edition e on e.id = card_sell.edition_id
where card_sell.direction = 'SELL' and card_buy.direction = 'BUY'
and (card_sell.price + @profit_limit) * @rate_usd_kc < card_buy.price
and card_sell.language = card_buy.language
and card_sell.quality = card_buy.quality
and card_sell.is_foil = card_buy.is_foil
and card_sell.price > 1
and exists (select * from editions_pair p where (card_sell.edition_id = p.edition2 and p.edition1 = card_buy.edition_id))) as a");

?>

<div class="right">
    <a href="match">Check mismatched editions [<?php echo $nonmatched_editions_cnt; ?>]</a>
</div>

<table id="myTable" class="display" data-order='[[ 6, "desc" ]]' data-page-length='100'>
<thead><tr>
    <td>Name<td>Foil?<td>Quality<td>Language<td>Edition<td>Pieces<td>Profit<td>Purchase Total
    <!--<td>Profit/Purchase ratio -->
</thead>
<tbody>
<?php

while ($row = $result->fetch()) {
    echo "<tr><td>$row[name]<td>$row[is_foil]<td>$row[quality]<td>$row[language]<td>$row[edition_name]<td>$row[pieces]<td>$row[profit]<td>$row[purchase_total]";
    //echo "<td>$row[profit_to_purchase]";
}

?>
</tbody>
<tfoot><tr>
    <td><td><td><td><td><td><td><td>
</tfoot></table>


<script type="text/javascript" src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    var $table = $('#myTable').DataTable();

    var columnsWithTotals = [5,6,7];

    for (key in columnsWithTotals) {
        var columnId = columnsWithTotals[key];
        var $span = $('<span />').appendTo(
            $table.column(columnId).footer()
        ).css('font-weight', 'bold');

        var sum = 0;
        $table.column(columnId).data().each(function(value) {
            sum += Number(value);
        });

        $span.html(Math.round(sum * 100) / 100);
    }
});

</script>

</body>
</html>