<?php
// Memulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "kidb";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi variabel untuk form
$id = "";
$judul_buku = "";
$penulis = "";
$kategori = "";
$tahun_terbit = "";
$penerbit = "";
$status = "";
$deskripsi = "";
$update = false;
$error = "";
$success = "";

// Create: Menambahkan buku baru
if (isset($_POST['save'])) {
    $judul_buku = $_POST['judul_buku'];
    $penulis = $_POST['penulis'];
    $kategori = $_POST['kategori'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $penerbit = $_POST['penerbit'];
    $status = $_POST['status'];
    $deskripsi = $_POST['deskripsi'];

    // Validasi input sederhana
    if (empty($judul_buku) || empty($penulis) || empty($kategori) || empty($tahun_terbit) || empty($status)) {
        $error = "Semua field wajib diisi kecuali deskripsi dan penerbit!";
    } else {
        $sql = "INSERT INTO buku (judul_buku, penulis, kategori, tahun_terbit, penerbit, status, deskripsi) 
                VALUES ('$judul_buku', '$penulis', '$kategori', '$tahun_terbit', '$penerbit', '$status', '$deskripsi')";

        if ($conn->query($sql) === TRUE) {
            $success = "Buku berhasil ditambahkan!";
            // Reset form
            $judul_buku = $penulis = $kategori = $tahun_terbit = $penerbit = $status = $deskripsi = "";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Read: Ambil data untuk diedit
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $update = true;

    $result = $conn->query("SELECT * FROM buku WHERE id=$id");
    if ($result->num_rows == 1) {
        $row = $result->fetch_array();
        $judul_buku = $row['judul_buku'];
        $penulis = $row['penulis'];
        $kategori = $row['kategori'];
        $tahun_terbit = $row['tahun_terbit'];
        $penerbit = $row['penerbit'];
        $status = $row['status'];
        $deskripsi = $row['deskripsi'];
    }
}

// Update: Perbarui buku
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $judul_buku = $_POST['judul_buku'];
    $penulis = $_POST['penulis'];
    $kategori = $_POST['kategori'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $penerbit = $_POST['penerbit'];
    $status = $_POST['status'];
    $deskripsi = $_POST['deskripsi'];

    if (empty($judul_buku) || empty($penulis) || empty($kategori) || empty($tahun_terbit) || empty($status)) {
        $error = "Semua field wajib diisi kecuali deskripsi dan penerbit!";
    } else {
        $sql = "UPDATE buku SET 
                judul_buku='$judul_buku', 
                penulis='$penulis', 
                kategori='$kategori', 
                tahun_terbit='$tahun_terbit', 
                penerbit='$penerbit', 
                status='$status', 
                deskripsi='$deskripsi' 
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $success = "Buku berhasil diperbarui!";
            $update = false;
            // Reset form
            $judul_buku = $penulis = $kategori = $tahun_terbit = $penerbit = $status = $deskripsi = "";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Delete: Hapus buku
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $sql = "DELETE FROM buku WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $success = "Buku berhasil dihapus!";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Read: Mengambil semua buku
$result = $conn->query("SELECT * FROM buku ORDER BY id DESC");

// Mencari buku
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $result = $conn->query("SELECT * FROM buku WHERE 
                          judul_buku LIKE '%$search%' OR 
                          penulis LIKE '%$search%' OR 
                          kategori LIKE '%$search%' OR 
                          penerbit LIKE '%$search%'
                          ORDER BY id DESC");
}

// Statistik untuk dashboard
$total_buku = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];
$total_tersedia = $conn->query("SELECT COUNT(*) as total FROM buku WHERE status='Tersedia'")->fetch_assoc()['total'];
$total_dipinjam = $conn->query("SELECT COUNT(*) as total FROM buku WHERE status='Dipinjam'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - Sistem Manajemen Perpustakaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            width: 250px;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .nav-link {
            color: #ced4da;
        }

        .nav-link:hover {
            color: white;
        }

        .nav-link.active {
            background-color: #495057;
            color: white;
        }

        .dashboard-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        h1 {
            color: #333;
        }

        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h3 class="text-center mb-4">Sistem Perpustakaan</h3>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-book me-2"></i> Manajemen Buku</a>
            </li>
        </ul>
        <hr>
        <div class="text-center">
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Header dashboard -->
            <div class="dashboard-container">
                <div class="header">
                    <h1>Dashboard</h1>
                </div>
                <p>Selamat datang, <?php echo $_SESSION['username']; ?>! Anda telah berhasil login.</p>
            </div>

            <!-- Statistik Dashboard -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Buku</h5>
                            <h2><?php echo $total_buku; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Buku Tersedia</h5>
                            <h2><?php echo $total_tersedia; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Buku Dipinjam</h5>
                            <h2><?php echo $total_dipinjam; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mb-4"><i class="fas fa-book me-2"></i> Manajemen Buku</h2>

            <!-- Search Form -->
            <div class="dashboard-container mb-4">
                <form method="get" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Cari judul buku, penulis, kategori..." name="search" value="<?php echo $search; ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                        <?php if ($search): ?>
                            <a href="dashboard.php" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Alert untuk menampilkan pesan -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Form untuk Create/Update buku -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><?php echo $update ? 'Edit Buku' : 'Tambah Buku Baru'; ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">

                                <div class="mb-3">
                                    <label for="judul_buku" class="form-label">Judul Buku</label>
                                    <input type="text" class="form-control" id="judul_buku" name="judul_buku"
                                        value="<?php echo $judul_buku; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="penulis" class="form-label">Penulis</label>
                                    <input type="text" class="form-control" id="penulis" name="penulis"
                                        value="<?php echo $penulis; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="" disabled selected>Pilih kategori</option>
                                        <option value="Fiksi" <?php if ($kategori == 'Fiksi') echo 'selected'; ?>>Fiksi</option>
                                        <option value="Non-Fiksi" <?php if ($kategori == 'Non-Fiksi') echo 'selected'; ?>>Non-Fiksi</option>
                                        <option value="Pendidikan" <?php if ($kategori == 'Pendidikan') echo 'selected'; ?>>Pendidikan</option>
                                        <option value="Komik" <?php if ($kategori == 'Komik') echo 'selected'; ?>>Komik</option>
                                        <option value="Biografi" <?php if ($kategori == 'Biografi') echo 'selected'; ?>>Biografi</option>
                                        <option value="Sejarah" <?php if ($kategori == 'Sejarah') echo 'selected'; ?>>Sejarah</option>
                                        <option value="Lainnya" <?php if ($kategori == 'Lainnya') echo 'selected'; ?>>Lainnya</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit"
                                        value="<?php echo $tahun_terbit; ?>" min="1900" max="<?php echo date('Y'); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="penerbit" class="form-label">Penerbit</label>
                                    <input type="text" class="form-control" id="penerbit" name="penerbit"
                                        value="<?php echo $penerbit; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="" disabled selected>Pilih status</option>
                                        <option value="Tersedia" <?php if ($status == 'Tersedia') echo 'selected'; ?>>Tersedia</option>
                                        <option value="Dipinjam" <?php if ($status == 'Dipinjam') echo 'selected'; ?>>Dipinjam</option>
                                        <option value="Dalam Perbaikan" <?php if ($status == 'Dalam Perbaikan') echo 'selected'; ?>>Dalam Perbaikan</option>
                                        <option value="Hilang" <?php if ($status == 'Hilang') echo 'selected'; ?>>Hilang</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo $deskripsi; ?></textarea>
                                </div>

                                <?php if ($update): ?>
                                    <button type="submit" name="update" class="btn btn-success w-100">Perbarui Buku</button>
                                <?php else: ?>
                                    <button type="submit" name="save" class="btn btn-primary w-100">Simpan Buku</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tabel untuk menampilkan buku (Read) -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Daftar Buku</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No.</th>
                                            <th>Judul Buku</th>
                                            <th>Penulis</th>
                                            <th>Kategori</th>
                                            <th>Tahun</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result->num_rows > 0) {
                                            $no = 1;
                                            while ($row = $result->fetch_assoc()) {
                                                $status_class = "";
                                                switch ($row['status']) {
                                                    case 'Tersedia':
                                                        $status_class = "text-success";
                                                        break;
                                                    case 'Dipinjam':
                                                        $status_class = "text-warning";
                                                        break;
                                                    case 'Dalam Perbaikan':
                                                        $status_class = "text-primary";
                                                        break;
                                                    case 'Hilang':
                                                        $status_class = "text-danger";
                                                        break;
                                                }

                                                echo "<tr>";
                                                echo "<td>" . $no++ . "</td>";
                                                echo "<td>" . $row['judul_buku'] . "</td>";
                                                echo "<td>" . $row['penulis'] . "</td>";
                                                echo "<td>" . $row['kategori'] . "</td>";
                                                echo "<td>" . $row['tahun_terbit'] . "</td>";
                                                echo "<td class='" . $status_class . "'>" . $row['status'] . "</td>";
                                                echo "<td>
                                                        <a href='?edit=" . $row['id'] . "' class='btn btn-warning btn-sm me-1'><i class='fas fa-edit'></i></a>
                                                        <a href='?delete=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Apakah Anda yakin ingin menghapus buku ini?\")'><i class='fas fa-trash'></i></a>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            if ($search) {
                                                echo "<tr><td colspan='7' class='text-center'>Tidak ada buku yang cocok dengan pencarian \"$search\"</td></tr>";
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>Tidak ada data buku</td></tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Tutup koneksi
$conn->close();
?>