<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/CustomerModel.php';

class CustomerController {
    private $model;

    public function __construct() {
        $this->model = new CustomerModel();
    }

    public function handleRequest() {
        if (isset($_POST['add_customer'])) {
            $nama = $_POST['nama_pelanggan'];
            $telp = $_POST['no_telepon'];
            if($this->model->addCustomer($nama, $telp)){
                echo "<script>alert('Pelanggan berhasil ditambahkan!'); window.location='customers.php';</script>";
                exit;
            }
        }

        if (isset($_POST['edit_customer'])) {
            $id = $_POST['id_pelanggan'];
            $nama = $_POST['nama_pelanggan'];
            $telp = $_POST['no_telepon'];
            if($this->model->updateCustomer($id, $nama, $telp)){
                echo "<script>alert('Data pelanggan berhasil diupdate!'); window.location='customers.php';</script>";
                exit;
            }
        }

        if (isset($_GET['delete_id'])) {
            $id = $_GET['delete_id'];
            if($this->model->deleteCustomer($id)){
                echo "<script>alert('Pelanggan berhasil dihapus (Soft Delete)!'); window.location='customers.php';</script>";
                exit;
            }
        }
    }

    public function getData() {
        $search = $_GET['search'] ?? '';
        $customers = $this->model->getAllCustomers($search);
        
        return [
            'search' => $search,
            'customers' => $customers
        ];
    }
}

$controller = new CustomerController();
$controller->handleRequest(); 
$data = $controller->getData(); 

extract($data);
?>