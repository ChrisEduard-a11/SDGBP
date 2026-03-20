<?php
$c = new mysqli('localhost', 'root', '', 'if0_38581055_sys_inv');
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
