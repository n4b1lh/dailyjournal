<?php
// memulai session atau melanjutkan session yang sudah ada
session_start();

// menghubungkan ke database
include("koneksi.php");

// check apakah ada session username
if (!isset($_SESSION['username']))  {
    // jika tidak ada, alihkan ke halaman login
    header("location:login.php");
    exit;
}

// Get current user data
$username = $_SESSION['username'];
$query = "SELECT id, username, foto FROM user WHERE username = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("location:login.php");
    exit;
}

$user_id = $user['id'];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    $foto = $_FILES['foto'] ?? null;
    
    // Handle password change
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE user SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if (!$update_stmt) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $update_stmt->bind_param('si', $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $message = 'Password berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui password.';
            }
            $update_stmt->close();
        }
    }
    
    // Handle photo upload
    if ($foto && $foto['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (in_array($foto['type'], $allowed_types)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = time() . '_' . basename($foto['name']);
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($foto['tmp_name'], $filepath)) {
                // Delete old photo if exists
                if ($user['foto'] && file_exists($user['foto'])) {
                    unlink($user['foto']);
                }

                $update_foto_query = "UPDATE user SET foto = ? WHERE id = ?";
                $foto_stmt = $conn->prepare($update_foto_query);
                
                if (!$foto_stmt) {
                    $error = 'Database error: ' . $conn->error;
                } else {
                    $foto_stmt->bind_param('si', $filepath, $user_id);
                    
                    if ($foto_stmt->execute()) {
                        $user['foto'] = $filepath;
                        $message = 'Foto profil berhasil diperbarui!';
                    } else {
                        $error = 'Gagal menyimpan foto ke database.';
                    }
                    $foto_stmt->close();
                }
            } else {
                $error = 'Gagal mengunggah foto.';
            }
        } else {
            $error = 'Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - My Daily Journal</title>
    <link rel="icon" href="img/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #content {
            flex: 1;
        }
        .photo-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- nav begin -->
    <nav class="navbar navbar-expand-sm bg-body-tertiary sticky-top bg-danger-subtle">
        <div class="container">
            <a class="navbar-brand" target="_blank" href=".">My Daily Journal</a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 text-dark">
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php?page=dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php?page=article">Article</a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php?page=gallery">Gallery</a>
                    </li> 
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-danger fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= $_SESSION['username']?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li> 
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li> 
                </ul>
            </div>
        </div>
    </nav>
    <!-- nav end -->

    <!-- content begin -->
    <section id="content" class="p-5">
        <div class="container">
            <h4 class="lead display-6 pb-2 border-bottom border-danger-subtle">Profile</h4>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Ganti Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Tuliskan Password Baru Jika Ingin Mengganti Password Saya">
                        </div>
                        
                        <div class="mb-3">
                            <label for="foto" class="form-label">Ganti Foto Profil</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Foto Profil Saat Ini</label>
                            <div>
                                <?php if ($user['foto'] && file_exists($user['foto'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto Profil" class="photo-preview">
                                <?php else: ?>
                                    <p class="text-muted fst-italic">Tidak ada foto profil</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- content end -->

    <!-- footer begin -->
    <footer class="text-center p-3 bg-danger-subtle">
        <div>
            <a href="https://www.instagram.com/udinusofficial"><i class="bi bi-instagram h2 p-2 text-dark"></i></a>
            <a href="https://twitter.com/udinusofficial"><i class="bi bi-twitter h2 p-2 text-dark"></i></a>
            <a href="https://wa.me/+62812685577"><i class="bi bi-whatsapp h2 p-2 text-dark"></i></a>
        </div>
        <div>Muhammad Nabil Haidar &copy; 2025</div>
    </footer>
    <!-- footer end -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>