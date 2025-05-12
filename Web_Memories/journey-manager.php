<?php
include("include/database.php");
include("include/function.php");
include("include/upload.php");

session_start();
$username = 'Khách';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit(); 
}
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $id_tk = $_SESSION['user_id'];
    $email = $_SESSION['email'];
} elseif (isset($_SESSION['facebook_name'])) {
    $username = $_SESSION['facebook_name'];
}
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hành trình yêu</title>
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <link rel="stylesheet" href="/web_memories/css/journey-mana.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateFileNameAndPreview() {
            const input = document.getElementById('profilePic');
            const label = document.getElementById('fileLabel');
            const imagePreview = document.getElementById('imagePreview');

            if (input.files.length > 0) {
                label.textContent = input.files[0].name;

                const file = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-50">';
                    imagePreview.style.display = 'block';
                }

                reader.readAsDataURL(file);
            } else {
                label.textContent = "Tải ảnh lên";
                imagePreview.style.display = 'none';
            }
        }
    </script>
    <?php
        $datacustomer = [];
        $datayourlove = [];
        if ($id_tk) {
            $datacustomer = ThongTinKhachHang($conn, $id_tk);
            $datayourlove = TimNguoiHenHo($conn, $email);
        }
        $datajourney = [];
        if($id_tk){
            $datajourney = DanhSachHanhTrinhYeu($conn, $datacustomer['ID_KH']);
        }
        
        $tieude = $noidung = $image = $ngay = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $tieude = filter_input(INPUT_POST, "title", FILTER_SANITIZE_SPECIAL_CHARS);
            $noidung = filter_input(INPUT_POST, "description", FILTER_SANITIZE_SPECIAL_CHARS);
            $ngay = $_POST['date'];
            $image = uploadImages('profilePic');
            if (empty($image)) {
                $image = null;
            }

            if(isset($_POST['journey_id'])){
                $journey_id = intval($_POST['journey_id']);
                $sql = "DELETE FROM CHITIETHANHTRINHYEU WHERE ID_HTY = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $journey_id);
                    $stmt->execute();
                    $stmt->close();
                    $sql1 = "DELETE FROM HANHTRINHYEU WHERE ID = ?";
                    $stmt1 = $conn->prepare($sql1);
                    if($stmt1){
                        $stmt1->bind_param("i", $journey_id);
                        $stmt1->execute();
                        $stmt1->close();
                    }
                }
                header("Location: journey-manager.php");
                exit;
            }
        
            // Kiểm tra xem có đang sửa hay không
            if (isset($_GET['edit_id'])) {
                // Trường hợp sửa
                $edit_id = intval($_GET['edit_id']);
                $sql = "UPDATE HANHTRINHYEU SET TIEUDE = ?, NOIDUNG = ?, NGAY = ?, ANH = ? WHERE ID = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssssi", $tieude, $noidung, $ngay, $image, $edit_id);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Trường hợp thêm mới
                $sql = "INSERT INTO HANHTRINHYEU (TIEUDE, NOIDUNG, NGAY, ANH, NGUOIVIET) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssss", $tieude, $noidung, $ngay, $image, $datacustomer['HOTEN']);
                    $stmt->execute();
                    $id_HTY = $stmt->insert_id;
                    $stmt->close();
        
                    $id_KH = $datacustomer['ID_KH'];
                    $id_yourlove = $datayourlove['ID_KH'];
        
                    $sql1 = "INSERT INTO CHITIETHANHTRINHYEU (ID_HTY, ID_KH) VALUES (?, ?)";
                    $stmt1 = $conn->prepare($sql1);
                    if ($stmt1) {
                        $stmt1->bind_param("ii", $id_HTY, $id_KH);
                        $stmt1->execute();
                        $stmt1->close();
                    }
        
                    if (!empty($id_yourlove)) {
                        $stmt2 = $conn->prepare($sql1);
                        if ($stmt2) {
                            $stmt2->bind_param("ii", $id_HTY, $id_yourlove);
                            $stmt2->execute();
                            $stmt2->close();
                        }
                    }
                }
            }
        
            header("Location: journey-manager.php");
            exit;
        }        
    ?>
</head>
<body>
<header>
    <?php
        include("include/header.php");
    ?>
</header>
<div class="container-journey-mana">
  <!-- Phần Thêm Cột Mốc Mới -->
  <section class="add-milestone">
    <h2><b>Thêm Cột Mốc Mới</b></h2>
    <form id="addMilestoneForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Tiêu Đề</label>
            <input type="text" id="title" name="title" placeholder="Nhập tiêu đề cột mốc" required>
        </div>
        <div class="form-group">
            <label for="date">Ngày</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div class="form-group">
            <label for="description">Mô Tả</label>
            <textarea id="description" name="description" rows="4" placeholder="Nhập mô tả cột mốc" required></textarea>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <label for="profilePic">Ảnh kỉ niệm:</label>
                        <div id="imagePreview" class="image-preview">
                            <?php if (!empty($diaryData['ANH_NK'])): ?>
                                <img src="<?php echo htmlspecialchars($diaryData['ANH_NK']); ?>" alt="Ảnh hành trình yêu" class="preview-image">
                            <?php else: ?>
                                <p>Hình ảnh sẽ được hiển thị tại đây.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-file-upload">
                            <label for="profilePic" id="fileLabel">📷 Tải ảnh lên</label>
                            <input type="file" id="profilePic" name="profilePic" accept="image/*" onchange="updateFileNameAndPreview()">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ngang">
            <button type="submit" class="btn-submit">Xác nhận</button>
        </div>
    </form>
  </section>

  <!-- Phần Danh Sách Cột Mốc -->
  <section class="milestone-list">
    <h2><b>Danh Sách Cột Mốc</b></h2>
    <div class="timeline">
      <!-- Mỗi Cột Mốc -->
       <?php
            if (!empty($datajourney)) {
                foreach ($datajourney as $row) {
                    echo '<div class="timeline-item">';
                    echo '<img src="' . htmlspecialchars($row['ANH']) . '" alt="Hình ảnh cột mốc">';
                    echo '<div class="timeline-content">';
                    echo '<h3>' . htmlspecialchars($row['TIEUDE']) . '</h3>';
                    echo '<p><b>Ngày: ' . htmlspecialchars($row['NGAY']) . '</b></p>';
                    echo '<p>' . htmlspecialchars($row['NOIDUNG']) . '</p>';
                    echo '</div>';
                    echo '<div class="timeline-actions">';
                    echo '<button type="button" class="edit-btn" 
                    data-id="' . htmlspecialchars($row['ID']) . '" 
                    data-title="' . htmlspecialchars($row['TIEUDE']) . '" 
                    data-date="' . htmlspecialchars($row['NGAY']) . '" 
                    data-description="' . htmlspecialchars($row['NOIDUNG']) . '" 
                    data-image="' . htmlspecialchars($row['ANH']) . '">
                    <i class="bi bi-pencil-square"></i>
                    </button>';
                    echo '<input type="hidden" name="diary_id" value="' . htmlspecialchars($row['ID']) . '">';
                    echo '<button type="button" class="delete-btn" data-id="' . htmlspecialchars($row['ID']) . '" data-toggle="modal" data-target="#confirmDeleteModal">
                        <i class="bi bi-trash"></i>
                        </button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Không có cột mốc nào.</p>';
            }
       ?>
    </div>
  </section>
</div>
<!-- Modal xác nhận xóa -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Xóa nhật ký?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa cột mốc này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn-primary" id="confirmDeleteButton">OK</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var journeyIdToDelete;

        $('.delete-btn').click(function() {
            journeyIdToDelete = $(this).data('id');
        });

        $('#confirmDeleteButton').click(function() {
            if (journeyIdToDelete) {
                $('<form>', {
                    'method': 'POST',
                    'action': '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>'
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': 'journey_id',
                    'value': journeyIdToDelete
                })).appendTo('body').submit();
            }
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const form = document.getElementById("addMilestoneForm");
        
        editButtons.forEach(button => {
            button.addEventListener("click", function() {
                const id = this.getAttribute("data-id");
                const title = this.getAttribute("data-title");
                const date = this.getAttribute("data-date");
                const description = this.getAttribute("data-description");
                const image = this.getAttribute("data-image");

                form.action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?edit_id=" + id;
                document.getElementById("title").value = title;
                document.getElementById("date").value = date;
                document.getElementById("description").value = description;

                const imagePreview = document.getElementById("imagePreview");
                if (image) {
                    imagePreview.innerHTML = '<img src="' + image + '" alt="Preview" class="w-50">';
                    imagePreview.style.display = 'block';
                } else {
                    imagePreview.innerHTML = '<p>Hình ảnh sẽ được hiển thị tại đây.</p>';
                }

                form.scrollIntoView({ behavior: "smooth" });
            });
        });
    });
</script>
</body>
</html>
