<?php
require_once '../models/menuModel.php';
$model = new menuModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id = $_POST['id_menu'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];
    $statusmenu = $_POST['statusmenu'];
    $foto_url = $_POST['foto_url'];
    $deskripsi = $_POST['deskripsi'];

    if ($id == "") {
        $model->create($nama, $harga, $id_kategori, $statusmenu, $foto_url, $deskripsi);
    } else {
        $model->update($id, $nama, $harga, $id_kategori, $statusmenu, $foto_url, $deskripsi);
    }

    header("Location: test.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    $model->delete($id);
    header("Location: test.php");
    exit;
}

$dataMenu = $model->read();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>

<body>

<div class="container mt-4">

    <button class="btn btn-success mb-2" 
            onclick="showTambah()">Tambah</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th><th>Nama</th><th>Harga</th><th>Kategori</th>
                <th>Status</th><th>Foto</th><th>Deskripsi</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataMenu as $row) { ?>
            <tr>
                <td><?php echo $row['id_menu']; ?></td>
                <td><?php echo $row['nama_menu']; ?></td>
                <td><?php echo $row['harga']; ?></td>
                <td><?php echo $row['id_kategori']; ?></td>
                <td><?php echo $row['statusmenu'] ? "Tersedia" : "Habis"; ?></td>
                <td><img src="<?php echo $row['foto_url']; ?>" width="50"></td>
                <td><?php echo $row['deskripsi']; ?></td>

                <td>
                    <button class="btn btn-primary btn-sm"
                        onclick="showEdit(
                            '<?php echo $row['id_menu']; ?>',
                            '<?php echo $row['nama_menu']; ?>',
                            '<?php echo $row['harga']; ?>',
                            '<?php echo $row['id_kategori']; ?>',
                            '<?php echo $row['statusmenu']; ?>',
                            '<?php echo $row['foto_url']; ?>',
                            `<?php echo htmlspecialchars($row['deskripsi']); ?>`
                        )">Edit</button>

                    <a href="test.php?action=delete&id=<?php echo $row['id_menu']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus data?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form method="POST">

                <input type="hidden" name="id_menu" id="id_menu">

                <div class="modal-body">

                    <label>Nama Menu</label>
                    <input type="text" class="form-control" name="nama" id="nama" required>

                    <label>Harga</label>
                    <input type="number" class="form-control" name="harga" id="harga" required>

                    <label>Kategori</label>
                    <input type="number" class="form-control" name="id_kategori" id="id_kategori">

                    <label>Status</label>
                    <select class="form-control" name="statusmenu" id="statusmenu">
                        <option value="true">Tersedia</option>
                        <option value="false">Habis</option>
                    </select>

                    <label>Foto URL</label>
                    <input type="text" class="form-control" name="foto_url" id="foto_url">

                    <label>Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>

            </form>

        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script>
function showTambah() {
    document.getElementById("modalTitle").innerText = "Tambah Menu";
    document.getElementById("id_menu").value = "";
    document.getElementById("nama").value = "";
    document.getElementById("harga").value = "";
    document.getElementById("id_kategori").value = "";
    document.getElementById("statusmenu").value = "true";
    document.getElementById("foto_url").value = "";
    document.getElementById("deskripsi").value = "";
    $('#formModal').modal('show');
}

function showEdit(id, nama, harga, kategori, status, foto, deskripsi) {
    document.getElementById("modalTitle").innerText = "Edit Menu";
    document.getElementById("id_menu").value = id;
    document.getElementById("nama").value = nama;
    document.getElementById("harga").value = harga;
    document.getElementById("id_kategori").value = kategori;
    document.getElementById("statusmenu").value = status === "t" ? "true" : "false";
    document.getElementById("foto_url").value = foto;
    document.getElementById("deskripsi").value = deskripsi;
    $('#formModal').modal('show');
}
</script>

</body>
</html>
