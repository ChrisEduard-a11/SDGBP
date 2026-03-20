<?php
$c = new mysqli('localhost', 'root', '', 'if0_38581055_sys_inv');
if ($c->connect_error) { die("Conn Error: " . $c->connect_error); }
$r = $c->query("SHOW COLUMNS FROM pagos");
while($row = $r->fetch_assoc()){
    echo $row['Field'] . ' : ' . $row['Type'] . "\n";
}
echo "\n====\n";
$r = $c->query("SHOW COLUMNS FROM usuario");
while($row = $r->fetch_assoc()){
    if ($row['Field'] == 'saldo') echo $row['Field'] . ' : ' . $row['Type'] . "\n";
}
?>
