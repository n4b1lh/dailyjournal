<?php
//query untuk mengambil data article
$sql1 = "SELECT * FROM article ORDER BY tanggal DESC";
$hasil1 = $conn->query($sql1);

$sql2 = "SELECT * FROM gallery ORDER BY tanggal DESC";
$hasil2 = $conn->query($sql2);

//menghitung jumlah baris data article
$jumlah_article = $hasil1->num_rows; 

//menghitung jumlah baris data gallery
$jumlah_gallery = $hasil2->num_rows; 

// Ambil data user dari database berdasarkan session username
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$query_user = "SELECT username, foto FROM user WHERE username = ?";
$stmt = $conn->prepare($query_user);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$foto = ($user && $user['foto']) ? $user['foto'] : 'https://via.placeholder.com/200';
$username_display = ($user) ? $user['username'] : $_SESSION['username'];

?>

<div class="text-center mb-5">
    <h2>Selamat Datang,</h2>
    <h1 class="text-danger fw-bold"><?php echo htmlspecialchars($username_display); ?></h1>
    <img src="<?php echo htmlspecialchars($foto); ?>" class="rounded-circle mt-3 mb-4" width="200" height="200" alt="User Photo">
</div>

<div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center pt-4">
    <div class="col">
        <div class="card border border-danger mb-3 shadow" style="max-width: 18rem;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="p-3">
                        <h5 class="card-title"><i class="bi bi-newspaper"></i> Article</h5> 
                    </div>
                    <div class="p-3">
                        <span class="badge rounded-pill text-bg-danger fs-2"><?php echo $jumlah_article; ?></span>
                    </div> 
                </div>
            </div>
        </div>
    </div> 
    <div class="col">
        <div class="card border border-danger mb-3 shadow" style="max-width: 18rem;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="p-3">
                        <h5 class="card-title"><i class="bi bi-camera"></i> Gallery</h5> 
                    </div>
                    <div class="p-3">
                        <span class="badge rounded-pill text-bg-danger fs-2"><?php echo $jumlah_gallery; ?></span>
                    </div> 
                </div>
            </div>
        </div>
    </div> 
</div>
