<?php
    require_once "connect_congress.php";

    $table = "congress";
    $dbc = mysqli_connect($hn, $un, $pw, $db);
    $fields = get_column_names($dbc, $table);
    mysqli_close($dbc);

    /* $rows = process($hn, $un, $pw, $db, $table); */

    print_top();
    print_form($fields);

    if (isset($_POST['submit'])) {
        $query = set_query($fields);
        $rows = process($query);
        $cols = isset($_POST['fields']) ? $_POST['fields'] : $fields;
        print_table($cols, $rows);
    }

    print_bottom();

function set_query($cols)
{
    $query = 'SELECT ';
    if (isset($_POST['submit'])) {
        if (isset($_POST['fields'])) {
            $fields = $_POST['fields'];
            if (count($fields)) {
                foreach ($fields as $field) {
                    $query .= "$field";
                    if ($field != end($fields)) $query .= ',';
                }
            }
        }
        else {
            $query .= ' *';
        }
        $query .= ' FROM congress';
        $where = FALSE;
        foreach ($cols as $col) {
            if (isset($_POST[$col])) {
                $match = sanitize_string($_POST[$col]);
                if (strlen($match)) {
                    if (!$where) {
                        $query .= ' WHERE';
                        $where = TRUE;
                    }
                    else {
                        $query .= ' AND';
                    }
                    $query .= " $col='$match'";
                }
            }
        }
        if (isset($_POST['order'])) {
            $key = $_POST['order'];
            $query .= " order by $key";
        }
        if (isset($_POST['dir'])) {
            $dir = $_POST['dir'];
            $query .= " $dir";
        }
    }
    return $query;
}

function process($query)
{
    $dbc = mysqli_connect('localhost', 'root', 'hotdlrny', 'congress');
    mysqli_set_charset($dbc, "utf8mb4");
    $result = mysqli_query($dbc, $query);
    mysqli_close($dbc);
    return $result;
}

function print_top()
{
    echo <<<_END
    <!DOCTYPE html>
    <html lang="la">
    <head>
    <meta charset="UTF-8">
    <!--link rel="stylesheet" href="/includes/style.css"-->
    <!--link rel="stylesheet" href="/includes/gradienttable.css"-->
    <link id="styleinfo" media="all">
    <style>label { display: inline-block;text-align: left; width: 125px; }</style>
    <title>U. S. Congress</title>
    <link rel="icon" href="capitol.ico" type="image/ico">
    <script src="congress.js"></script>
    </head>
    <body style="background-color:powderblue">
    <header><p>U. S. Congress Database</p></header>
    <!--header><p>Josh Roybal</p></header-->
    <!--h1>U. S. Congress</h1-->

_END;
}

function print_form($fields)
{
    echo "choose fields<br>\r\n";
    echo "<form action='congress.php' method='POST'>\r\n";
    $checked = isset($_POST['fields']) ? $_POST['fields'] : [];
    foreach ($fields as $field) {
        $val = in_array($field, $checked) ? 'checked' : '';
        echo "<input type='checkbox' name='fields[]' value='$field' $val> $field ";
    }
    echo "<br>\r\n";
    echo "match values<br>\r\n";
    foreach ($fields as $field) {
        $val = (isset($_POST[$field])) ? $_POST[$field] : '';
        // echo "<label>$field: </label><input type='text' name='$field' value='$val'><br>\r\n";
        echo "<label>$field: </label><input type='text' name='$field' value='$val'>";
    }
    echo "<br>\r\n";
    echo "sort key<br>\r\n";
    $checked = isset($_POST['order']) ? $_POST['order'] : '';
    foreach ($fields as $field) {
        $val = $field == $checked ? 'checked' : '';
        echo "<input type='radio' name='order' value='$field' $val>$field";
    }
    echo "<br>\r\n";
    $checked = isset($_POST['dir']) ? $_POST['dir'] : '';
    $val = $checked == 'asc' ? 'checked' : '';
    echo "<input type='radio' name='dir' value='asc' $val>ascending";
    $val = $checked == 'desc' ? 'checked' : '';
    echo "<input type='radio' name='dir' value='desc' $val>descending";
    echo "<br>\r\n";
    echo "<input type='submit' name='submit' value='submit'>\r\n";
    echo "</form>\r\n";
}

function print_list($fields, $records)
{
    echo "<ul>\r\n";
    foreach ($records as $record) {
        echo "<li>\r\n";
        print_record($fields, $record);
        echo "</li>\r\n";
    }
    echo "</ul>\r\n";
}

function print_record($fields, $record)
{
    $items = array_combine($fields, $record);
    foreach ($items as $field => $value) {
        echo "$field: $value<br>\r\n";
    }
}

function print_table($hdrs, $rows)
{
    // $n = mysqli_num_rows($rows);
    // echo $n;
    echo "<table id='myTable'>\r\n";
    print_headers($hdrs);
    foreach ($rows as $row) {
        print_row($row);
    }
    echo "</table>\r\n";
}

function print_headers($hdrs)
{
    echo '<tr>';
    foreach ($hdrs as $hdr) {
        echo "<th>$hdr</th>";
    }
    echo "</tr>\r\n";
}

function print_row($cols)
{
    echo '<tr>';
    foreach ($cols as $col) {
        echo "<td>$col</td>";
    }
    echo "</tr>\r\n";
}

function print_bottom()
{
    echo <<<_END
    <footer><p>Copyright &copy 2023 Josh Roybal.</p></footer>
    </body>
    </html>
_END;
}

function get_column_names($connection, $table_name)
{
    $result = mysqli_query($connection, "SHOW COLUMNS FROM $table_name");
    $col_names = [];
    foreach ($result as $item) {
         array_push($col_names, $item['Field']);
    }
    return $col_names;
}

function update_record($conn, $table, $kid, $rec, $fields)
{
    $arr = array_combine($fields, $rec);
    $query = "UPDATE $table SET";
    foreach ($arr as $field => $value) {
        $query .= " $field='$value'";
        if ($field != array_key_last($arr)) {
            $query .= ',';
        }
    }
    $query .= " WHERE key_id='$kid'";
    echo "$query<br>\r\n";
    $result = mysqli_query($conn, $query);
    echo "<br>\r\n";
    var_dump($result);
    echo "<br>\r\n";
}

function sanitize_string($var)
{
    if (get_magic_quotes_gpc())
        $var = stripcslashes($var);
    $var = strip_tags($var);
    $var = htmlentities($var);
    return $var;
}

function sanitize_mysql($connection, $var)
{
    return sanitize_string(mysqli_escape_string($connection, $var));
}

?>
