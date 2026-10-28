<?php

require_once "connection.php";

// Menjalankan fungsi berdasarkan parameter function
if (isset($_GET['function']) && function_exists($_GET['function'])) {
    $_GET['function']();
}

// 1. Fungsi mengambil semua data karyawan
function getKaryawan()
{
    global $connect;

    $data = [];
    $query = $connect->query("SELECT * FROM tbl_karyawan");

    while ($row = mysqli_fetch_object($query)) {
        $data[] = $row;
    }

    $response = [
        'status' => 200,
        'message' => 'Success',
        'data' => $data
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
}

// 2. Fungsi menambahkan data karyawan
function insertKaryawan()
{
    global $connect;

    $nama = $_POST['nama'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $handphone = $_POST['handphone'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    if (
        $nama === '' ||
        !in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'], true) ||
        $handphone === '' ||
        $alamat === ''
    ) {
        http_response_code(400);
        $response = [
            'status' => 400,
            'message' => 'Parameter tidak lengkap atau tidak valid'
        ];
    } else {
        $stmt = $connect->prepare(
            "INSERT INTO tbl_karyawan (nama, jenis_kelamin, handphone, alamat)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $nama,
            $jenis_kelamin,
            $handphone,
            $alamat
        );

        if ($stmt->execute()) {
            http_response_code(201);
            $response = [
                'status' => 201,
                'message' => 'Insert Success',
                'id' => $connect->insert_id
            ];
        } else {
            http_response_code(400);
            $response = [
                'status' => 400,
                'message' => 'Insert Failed'
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

// 3. Fungsi mengambil data karyawan berdasarkan ID
function getKaryawanById()
{
    global $connect;

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        $response = [
            'status' => 400,
            'message' => 'ID tidak valid'
        ];
    } else {
        $stmt = $connect->prepare(
            "SELECT * FROM tbl_karyawan WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            $response = [
                'status' => 200,
                'message' => 'Success',
                'data' => $data
            ];
        } else {
            http_response_code(404);
            $response = [
                'status' => 404,
                'message' => 'No Data Found'
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

// 4. Fungsi memperbarui data karyawan
function updateKaryawan()
{
    global $connect;

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $nama = $_POST['nama'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $handphone = $_POST['handphone'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    if (
        !$id || $id < 1 ||
        $nama === '' ||
        !in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'], true) ||
        $handphone === '' ||
        $alamat === ''
    ) {
        http_response_code(400);
        $response = [
            'status' => 400,
            'message' => 'Parameter tidak lengkap atau tidak valid'
        ];
    } else {
        $stmt = $connect->prepare(
            "UPDATE tbl_karyawan
             SET nama = ?, jenis_kelamin = ?, handphone = ?, alamat = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssi",
            $nama,
            $jenis_kelamin,
            $handphone,
            $alamat,
            $id
        );

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $response = [
                'status' => 200,
                'message' => 'Update Success'
            ];
        } else {
            http_response_code(400);
            $response = [
                'status' => 400,
                'message' => 'Update Failed'
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

// 5. Fungsi menghapus data karyawan
function deleteKaryawan()
{
    global $connect;

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        $response = [
            'status' => 400,
            'message' => 'ID tidak valid'
        ];
    } else {
        $stmt = $connect->prepare(
            "DELETE FROM tbl_karyawan WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $response = [
                'status' => 200,
                'message' => 'Delete Success'
            ];
        } else {
            http_response_code(404);
            $response = [
                'status' => 404,
                'message' => 'Data tidak ditemukan atau gagal dihapus'
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
