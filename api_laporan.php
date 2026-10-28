<?php
header("Content-Type: application/json");
include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $query = "SELECT id, tanggal, ref, produk, total_qty AS totalQty, total, tunai, kembalian FROM laporan ORDER BY id DESC";
        $result = $conn->query($query);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['totalQty'] = (int)$row['totalQty'];
                $row['total'] = (int)$row['total'];
                $row['tunai'] = (int)$row['tunai'];
                $row['kembalian'] = (int)$row['kembalian'];
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST':
        $stmt = $conn->prepare("INSERT INTO laporan (tanggal, ref, produk, total_qty, total, tunai, kembalian) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiii", $input['tanggal'], $input['ref'], $input['produk'], $input['totalQty'], $input['total'], $input['tunai'], $input['kembalian']);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        break;

    case 'DELETE':
        $conn->query("DELETE FROM laporan");
        echo json_encode(["status" => "success"]);
        break;
}
$conn->close();
?>