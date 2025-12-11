<?php
session_start();
require_once '../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function checkLogin() {
        if (isset($_SESSION['user_logged_in'])) {
            header("Location: homepage.php");
            exit;
        }
    }

    public function handleLogin() {
        $error = "";
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $user = $this->userModel->login($username, $password);

            if ($user) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id']  = $user['id'];      
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname']; 
                $_SESSION['role']     = $user['role'];

                header("Location: homepage.php");
                exit;
            } else {
                $error = "Username atau Password salah!";
            }
        }
        return $error;
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>