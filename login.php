<?php
// Memulai session
session_start();

// Konfigurasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kidb";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Membuat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    echo "Error membuat database: " . $conn->error;
}

// Memilih database
$conn->select_db($dbname);

// Membuat tabel users jika belum ada
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    password VARCHAR(30) NOT NULL
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error membuat tabel: " . $conn->error;
}

// Menambahkan user default jika belum ada
$sql = "INSERT INTO users (username, password) 
        SELECT 'admin', 'admin123' 
        FROM dual 
        WHERE NOT EXISTS (SELECT * FROM users WHERE username = 'admin')";

if ($conn->query($sql) !== TRUE) {
    echo "Error menambahkan user default: " . $conn->error;
}

// Cek apakah user sudah login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

// Proses login
$login_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Login berhasil, set session dan redirect ke dashboard
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        header("Location: dashboard.php");
        exit;
    } else {
        $login_message = "<div style='color: red;'>Login gagal. Username atau password salah.</div>";
    }
}

// Menutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Perpustakaan Kita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: rgb(61, 22, 234);
            color: white;
            padding: 10px 15px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: rgb(2, 67, 95);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <?php echo $login_message; ?>
    </div>
</body>

</html>

<!-- SQL Injection Command -->
<!-- username : ' OR '1'='1 password : ' OR '1'='1 -->
<!-- username : ' OR 1=1 # password : bebas -->