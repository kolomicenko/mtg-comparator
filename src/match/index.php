<?php

// TODO enable multiselects and pairing one edition with more editions from other shop?

require_once "../connect.php";

// create new pair
if (isset($_POST['shop']) && is_array($_POST['shop'])) {
    $pair = array();

    foreach ($_POST['shop'] as $edition_id) {
        // TODO create all possible pairs when there are more than 2 shops supported
        // TODO verify that the editions belong to the shops
        if (is_numeric($edition_id)) {
            $pair[] = $edition_id;
        }
    }

    $status = 0;

    if (count($pair) == 2) {
        query('INSERT INTO editions_pair(edition1, edition2) VALUES(?, ?), (?, ?)',
                $pair[0], $pair[1], $pair[1], $pair[0]);

        $status = 1;
    }

    header("location: $_SERVER[SCRIPT_NAME]?status=$status");
    exit;
}

// delete a pair
if (isset($_POST['delete']) && is_array($_POST['delete'])) {
    $pair = array();

    foreach ($_POST['delete'] as $edition_id) {
        // TODO create all possible pairs when there are more than 2 shops supported
        if (is_numeric($edition_id)) {
            $pair[] = $edition_id;
        }
    }

    $status = 0;

    if (count($pair) == 2) {
        query('DELETE FROM editions_pair WHERE edition1 = ? AND edition2 = ? OR edition1 = ? AND edition2 = ?',
                $pair[0], $pair[1], $pair[1], $pair[0]);

        $status = 2;
    }

    header("location: $_SERVER[SCRIPT_NAME]?status=$status");
    exit;
}

// show status
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 0:
            $message = "Please set both editions.";
            $color = "red";
            break;
        case 1:
            $message = "Paired successfully.";
            $color = "green";
            break;
        case 2:
            $message = "Pair deleted successfully.";
            $color = "green";
            break;
        default:
            $message = "An error occurred.";
            $color = "red";
    }

    ?> <div style="width: 100%; text-align: center; padding: 5px; color: <?php echo $color; ?>;" ><?php echo $message; ?></div> <?php
}


// get and display non matched editions
$not_matched_editions = query('select e.id as edition_id, s.id as shop_id, e.name as edition_name from edition e join shop s on e.shop_id = s.id where not exists (select * from editions_pair where edition1 = e.id) order by edition_name');

$shops_result = query('select id, name from shop');

// init shops
$shops = array();
$editions_by_shops = array();
while ($shop = $shops_result->fetch()) {
    $shops[$shop['id']] = $shop['name'];
    $editions_by_shops[$shop['id']] = array();
}

while ($edition = $not_matched_editions->fetch()) {
    $editions_by_shops[$edition['shop_id']][] = array(
        'shop_id' => $edition['shop_id'],
        'edition_id' => $edition['edition_id'],
        'edition_name' => $edition['edition_name']
    );
}

?>

<html>
<head>

<style type="text/css">

input[type=image] {
    height: 20px;
}

select[name^=shop] {
    width: 100%;
}

form {
    margin: 0px;
}

</style>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.css">

</head>

<body>

<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>

<table id="table_editions" class="display" data-order='[[ 0, "asc" ]]' data-page-length='100'>
    <thead>
        <tr>
<?php

foreach ($shops as $shop_name) {
    echo "<td>$shop_name";
}

?>
        <td>Create / Delete
    </thead>
    <tbody>
    <tr>
<?php

foreach ($editions_by_shops as $shop_id => $editions) {
    ?>
    <td><select name="shop[<?php echo $shop_id; ?>]">
    <option value="">

    <?php
    foreach ($editions as $edition) {
        ?><option value="<?php echo $edition['edition_id']; ?>"><?php echo $edition['edition_name'];
    }

    ?>
    </select>
    <?php
}

?>
    <td><form id="create_form" method="post" onsubmit="return create_new_pair();"><input type="image" src="create-icon.png" alt="Create pair" name="match"></form>


<?php

// get and display matched editions
$matched_editions = query('select e1.id as e1_id, e1.name as e1_name, e1.shop_id as e1_shop_id, e2.id as e2_id, e2.name as e2_name, e2.shop_id as e2_shop_id from editions_pair p join edition e1 on e1.id = p.edition1 join edition e2 on e2.id = p.edition2 where e1.shop_id < e2.shop_id order by e1.name');

while ($editions_pair = $matched_editions->fetch()) {
    ?> <tr><td><?php echo $editions_pair['e1_name']; ?><td><?php echo $editions_pair['e2_name']; ?>
        <td>
            <form method="post">
                <input type="hidden" name="delete[0]" value="<?php echo $editions_pair['e1_id']; ?>">
                <input type="hidden" name="delete[1]" value="<?php echo $editions_pair['e2_id']; ?>">

                <input type="image" src="delete-icon.png" alt="Delete pair" >
            </form>

        <?php
}

?>

</tbody>
</table>

<script type="text/javascript" src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">

$(document).ready(function(){
    var $table = $('#table_editions').DataTable();
});

var create_new_pair = function() {
    var $form = $('#create_form');
    var can_submit = true;

    $form.parents('tr').find('select').each(function(){
        $select = $(this);
        if ($select.val() == "") {
            can_submit = false;
            return false; // break
        }

        $input = $('<input type="hidden" />').attr('name', $select.attr('name')).attr('value', $select.val()).appendTo($form);
    });

    return can_submit;
}

</script>

</body>
</html>
