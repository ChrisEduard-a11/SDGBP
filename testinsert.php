<?php
$c = new mysqli('localhost', 'root', '', 'if0_38581055_sys_inv');
$stmt = $c->prepare("INSERT INTO pagos (nombre_cliente, monto, descripcion, referencia, fecha_pago, estado, tipo, cliente, saldo_resultante, metodo_pago) VALUES ('Test', ?, 'Desc', 'REF', NOW(), 'pendiente', 'Ingreso', 'Cliente', 0, 'Metodo')");

$m1 = (float)"25.87";
$stmt->bind_param("d", $m1);
$stmt->execute();
echo "Inserted 25.87: ";

$m2 = floatval("25,87");
$stmt->bind_param("d", $m2);
$stmt->execute();
echo "Inserted floatval(25,87): ";

$r = $c->query("SELECT id, monto FROM pagos WHERE nombre_cliente='Test'");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . " : " . $row['monto'] . "\n";
}
$c->query("DELETE FROM pagos WHERE nombre_cliente='Test'");
?>
