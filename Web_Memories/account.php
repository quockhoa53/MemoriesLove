<?php
include("include/database.php");
include("include/function.php");

session_start();
$username = 'Khách';
$id_tk = null;
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
$datacustomer = [];
if ($id_tk) {
    $datacustomer = ThongTinKhachHang($conn, $id_tk);
}
$datayourlove = [];
if ($id_tk) {
    $datayourlove = TimNguoiHenHo($conn, $email);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <link rel="stylesheet" href="/web_memories/css/account.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.navbar-nav li').click(function() {
                $('.navbar-nav li').removeClass('active');
                $(this).addClass('active');
            });

            $('.sidebar ul li a').click(function(event) {
                if ($(this).attr('href') === 'logout.php') {
                    return true;
                }
                event.preventDefault();

                $('.content-section').hide();

                $('.sidebar .nav-link').removeClass('active');

                const targetId = $(this).data('target');
                $('#' + targetId).show();

                $(this).addClass('active');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const verticalMenu = document.querySelector('.vertical-menu');

            menuToggle.addEventListener('click', function() {
                if (verticalMenu.style.display === 'block') {
                    verticalMenu.style.display = 'none';
                } else {
                    verticalMenu.style.display = 'block';
                }
            });
        });
    </script>
    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    <?php
        $name = $phone = $dob = $gender = $address = $ava = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $maKH = generateUniqueCodes();
            if (isset($_POST['changePassword'])) {
                $currentPassword = filter_input(INPUT_POST, "currentPassword", FILTER_SANITIZE_SPECIAL_CHARS);
                $newPassword = filter_input(INPUT_POST, "newPassword", FILTER_SANITIZE_SPECIAL_CHARS);
                $confirmPassword = filter_input(INPUT_POST, "confirmPassword", FILTER_SANITIZE_SPECIAL_CHARS);

                $sql = "SELECT PASSWORD FROM TAIKHOAN WHERE ID_TK = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_tk);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $hashedPassword = $user['PASSWORD'];
                $stmt->close();

                if (password_verify($currentPassword, $hashedPassword)) {
                    if ($newPassword === $confirmPassword) {
                        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $sql = "UPDATE TAIKHOAN SET PASSWORD = ? WHERE ID_TK = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $newHashedPassword, $id_tk);
                        if ($stmt->execute()) {
                            echo "Mật khẩu đã được cập nhật thành công.";
                        } else {
                            echo "Có lỗi xảy ra. Vui lòng thử lại.";
                        }
                        $stmt->close();
                    } else {
                        echo "Mật khẩu mới và xác nhận mật khẩu không khớp.";
                    }
                } else {
                    echo "Mật khẩu hiện tại không chính xác.";
                }
            } 

            elseif(isset($_POST['action']) && $_POST['action'] === 'update'){
                $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
                $gender = filter_input(INPUT_POST, "gender", FILTER_SANITIZE_SPECIAL_CHARS);
                $dob = filter_input(INPUT_POST, "dob", FILTER_SANITIZE_SPECIAL_CHARS); 
                $address = filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
                $phone = filter_input(INPUT_POST, "phone", FILTER_SANITIZE_SPECIAL_CHARS);
                
                if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == 0) {
                    $uploadDir = 'upload/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $uploadFile = $uploadDir . basename($_FILES['profilePic']['name']);

                    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $uploadFile)) {
                        $ava = $uploadFile;
                    } else {
                        echo "Không thể tải lên ảnh.";
                    }
                }
                if($ava == null){
                    $ava = $datacustomer['AVARTAR'];
                }
                $sql = "INSERT INTO KHACHHANG (HOTEN, SDT, NGAYSINH, GIOITINH, DIACHI, ID_TK, AVARTAR, MAKH) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE HOTEN=?, SDT=?, NGAYSINH=?, GIOITINH=?, DIACHI=?, AVARTAR=?";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("ssssssssssssss", $name, $phone, $dob, $gender, $address, $id_tk, $ava, $maKH, $name, $phone, $dob, $gender, $address, $ava);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            elseif(isset($_POST['henho_id'])){
                $sql = "CALL SP_HUYHENHO(?, ?)";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param('ii', $datacustomer['ID_KH'], $datayourlove['ID_KH']);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
        }
        $datacustomer = [];
        if ($id_tk) {
            $datacustomer = ThongTinKhachHang($conn, $id_tk);
        }
        $datayourlove = [];
        if ($id_tk) {
            $datayourlove = TimNguoiHenHo($conn, $email);
        }
        ?>
        <?php
            $dataSearch = [];
            $query = '';
            if (isset($_GET['query'])) {
                $query = $_GET['query'];
                $dataSearch = NoiDungTimKiem($conn, $query, $datacustomer['ID_KH']);
            }
        ?>
</head>
<body>
    <header>
        <?php
            include("include/header.php");
        ?>
    </header>
    <br>
    <div class="container">
    <?php if ($query){ ?>
            <h2 style="color: black;">Kết quả tìm kiếm cho <i>"<?php echo htmlspecialchars($query); ?>"</i></h2>
            <div id="result" class="search-container">
                <?php
                    include("include/search.php");
                ?>
        </div>
        <?php } else{ ?>
    <div class="container-account">
        <nav class="sidebar">
            <ul>
                <li><a href="#" class="nav-link" data-target="personalInfo">Thông tin cá nhân</a></li>
                <li><a href="#" class="nav-link" data-target="changePassword">Đổi mật khẩu</a></li>
                <li><a href="logout.php" class="nav-link">Đăng xuất</a></li>
            </ul>
        </nav>
        <main>
            <div id="personalInfo" class="content-section">
                <h2>Thông tin cá nhân</h2>
                <form id="accountForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <div class="form-group">
                        <div id="profilePicContainer">
                            <div id="profilePicPreview">
                                <?php if (!empty($datacustomer['AVARTAR'])): ?>
                                    <img src="<?php echo htmlspecialchars($datacustomer['AVARTAR']); ?>" alt="Ảnh đại diện" id="avatarImage">
                                <?php else: ?>
                                    <img src="default-avatar.png" alt="Ảnh đại diện" id="avatarImage">
                                <?php endif; ?>
                            </div>
                            <input type="file" id="profilePic" name="profilePic" accept="image/*">
                            <label for="profilePic" class="upload-icon">
                                <b><i class="bi bi-upload"></i></b>
                            </label>
                        </div>
                    </div>
                    <!-- JavaScript để xử lý ảnh upload -->
                    <script>
                        document.getElementById('profilePic').addEventListener('change', function(event) {
                            var file = event.target.files[0];
                            if (file) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    document.getElementById('avatarImage').src = e.target.result;
                                }
                                reader.readAsDataURL(file);
                            }
                        });
                    </script>
                    <div class="form-group">
                        <?php
                            if(!empty($datayourlove['HOTEN'])){
                                echo '<p>❤ Đang hẹn hò với <span><b>'.htmlspecialchars($datayourlove['HOTEN']).'</b></span></p>';
                                echo '<input type="hidden" name="henho_id" value="'.htmlspecialchars($datacustomer['ID_HH']).'">';
                                echo '<button type="button" class="huy-henho" data-id="'.htmlspecialchars($datacustomer['ID_HH']).'" data-toggle="modal" data-target="#confirmDeleteModal">
                                      Hủy hẹn hò</button>';
                            }
                        ?>
                    </div>
                    <div class="form-group">
                        <?php
                            if(!empty($datacustomer['ID_KH'])){
                                echo '<label for="id">ID cá nhân </label>';
                                echo '<input type="text" id="id" name="id" value="'.htmlspecialchars($datacustomer['MAKH']).'" readonly required>';
                            }
                        ?>
                    </div>
                    <div class="form-group">
                        <label for="name">Họ và tên *</label>
                        <input type="text" id="name" name="name" value="<?php echo !empty($datacustomer['HOTEN']) ? htmlspecialchars($datacustomer['HOTEN']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Giới tính</label>
                        <input type="radio" id="female" name="gender" value="Nữ" <?php echo (!empty($datacustomer['GIOITINH']) && $datacustomer['GIOITINH'] === 'Nữ') ? 'checked' : ''; ?>> Nữ
                        <input type="radio" id="male" name="gender" value="Nam" <?php echo (!empty($datacustomer['GIOITINH']) && $datacustomer['GIOITINH'] === 'Nam') ? 'checked' : ''; ?>> Nam
                    </div>
                    <div class="form-group">
                        <label for="dob">Ngày sinh *</label>
                        <input type="date" id="dob" name="dob" value="<?php echo !empty($datacustomer['NGAYSINH']) ? htmlspecialchars($datacustomer['NGAYSINH']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Địa chỉ *</label>
                        <input type="text" id="address" name="address" value="<?php echo !empty($datacustomer['DIACHI']) ? htmlspecialchars($datacustomer['DIACHI']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">SĐT *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo !empty($datacustomer['SDT']) ? htmlspecialchars($datacustomer['SDT']) : ''; ?>" required>
                    </div>
                    <button type="submit" id ="submit" name="submit">Xác nhận</button>
                </form>
            </div>

            <div id="changePassword" class="content-section">
                <h2>Đổi mật khẩu</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="form-group">
                        <label for="currentPassword">Mật khẩu hiện tại *</label>
                        <input type="password" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Mật khẩu mới *</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Xác nhận mật khẩu mới *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <button type="submit" name="changePassword">Đổi mật khẩu</button>
                </form>
            </div>
        </main>
        <?php } ?>
    </div>
    </div>
    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Xác nhận chia tay?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Lưu ý sau khi chia tay mọi kỉ niệm cùng nhau của 2 bạn sẽ bị mất. Bạn vẫn chắc chắn muốn chia tay chứ?
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
            var henhoToDelete;

            $('.huy-henho').click(function() {
                henhoToDelete = $(this).data('id');
            });

            $('#confirmDeleteButton').click(function() {
                if (henhoToDelete) {
                    $('<form>', {
                        'method': 'POST',
                        'action': '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>'
                    }).append($('<input>', {
                        'type': 'hidden',
                        'name': 'henho_id',
                        'value': henhoToDelete
                    })).appendTo('body').submit();
                }
            });
        });
    </script>
</body>
</html>
