<?php
header("Content-Type: application/json");
include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET': 
        $query = "SELECT kode, nama, kategori, ukuran, harga, stok, (UNIX_TIMESTAMP(updated_at) * 1000) AS updatedAt FROM inventaris";
        $result = $conn->query($query);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['harga'] = (int)$row['harga'];
                $row['stok'] = (int)$row['stok'];
                $row['updatedAt'] = (int)$row['updatedAt'];
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST': 
        $stmt = $conn->prepare("INSERT INTO inventaris (kode, nama, kategori, ukuran, harga, stok) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $input['kode'], $input['nama'], $input['kategori'], $input['ukuran'], $input['harga'], $input['stok']);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        break;

    case 'PUT': 
        $stmt = $conn->prepare("UPDATE inventaris SET nama=?, kategori=?, ukuran=?, harga=?, stok=? WHERE kode=?");
        $stmt->bind_param("sssiis", $input['nama'], $input['kategori'], $input['ukuran'], $input['harga'], $input['stok'], $input['kode']);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        break;

    case 'DELETE': 
        $kode = $_GET['kode'];
        $stmt = $conn->prepare("DELETE FROM inventaris WHERE kode=?");
        $stmt->bind_param("s", $kode);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
        break;
}
$conn->close();
?>