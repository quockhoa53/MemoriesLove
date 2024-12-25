<?php
include("include/database.php");
include("include/function.php");
include("include/upload.php");

session_start();
$username = 'Khách';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $id_tk = $_SESSION['user_id'];
} elseif (isset($_SESSION['facebook_name'])) {
    $username = $_SESSION['facebook_name'];
}
$noidung = $tieude = $img = "";
$diaryId = isset($_GET['diary_id']) ? $_GET['diary_id'] : '';
$diaryData = [];
if ($diaryId) {
    $diaryData = LayNhatKyTheoID($conn, $diaryId);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_nk = isset($_POST['id_nk']) ? $_POST['id_nk'] : '';
    if ($id_nk) {
        $diaryDataTemp = LayNhatKyTheoID($conn, $id_nk);
    }
    $tieude = filter_input(INPUT_POST, "title", FILTER_SANITIZE_SPECIAL_CHARS);
    $noidung = $_POST['content'];
    $img = uploadImages('profilePic');
    if (empty($img) && isset($diaryDataTemp['ANH_NK'])) {
        $img = $diaryDataTemp['ANH_NK'];
    }
    if (isset($_POST['id_nk']) && !empty($_POST['id_nk'])) {
        $sql = "UPDATE NHATKY SET TIEUDE = ?, NOIDUNG = ?, ANH_NK = ? WHERE ID_NK = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssi", $tieude, $noidung, $img, $id_nk);
            $stmt->execute();
            $stmt->close();
        }

    } else {
        $datacustomer = ThongTinKhachHang($conn, $id_tk);
        $sql = "INSERT INTO NHATKY (TIEUDE, NOIDUNG, ANH_NK, ID_KH) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssi", $tieude, $noidung, $img, $datacustomer['ID_KH']);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['form_submitted'] = true;
    header("Location: diary.php");
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <link rel="stylesheet" href="/web_memories/css/diary.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.navbar-nav li').click(function() {
                $('.navbar-nav li').removeClass('active');
                $(this).addClass('active');
            });

            document.querySelector('.menu-toggle').addEventListener('click', function() {
                const verticalMenu = document.querySelector('.vertical-menu');
                verticalMenu.style.display = (verticalMenu.style.display === 'block') ? 'none' : 'block';
            });
        });

        function showDiaryForm() {
            window.location.href = 'diary.php';
        }
    </script>
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
</head>
<body>
<header>
<?php
    include("include/header.php");
?>
</header>
<main>
    <div class="container">
        <form class="diary-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
            <input type="hidden" name="id_nk" value="<?php echo htmlspecialchars($diaryId); ?>">
            <div class="form-group">
                <label for="title">Tiêu đề:</label>
                <input type="title" id="title" name="title" value="<?php echo isset($diaryData['TIEUDE']) ? htmlspecialchars($diaryData['TIEUDE']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Nội dung:</label>
                <textarea id="content" name="content" placeholder="Bạn đang nghĩ gì?..." required><?php echo isset($diaryData['NOIDUNG']) ? htmlspecialchars($diaryData['NOIDUNG']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="profilePic">Ảnh nhật ký:</label>
                <div id="imagePreview" class="image-preview"></div>
                <div class="custom-file-upload">
                    <label for="profilePic" id="fileLabel">Tải ảnh lên</label>
                    <input type="file" id="profilePic" name="profilePic" accept="image/*" onchange="updateFileNameAndPreview()">
                </div>
            </div>
            <div class="form-group">
                <?php if (!empty($diaryData['ANH_NK'])): ?>
                    <img src="<?php echo htmlspecialchars($diaryData['ANH_NK']); ?>" alt="Ảnh nhật ký hiện tại" class="preview-image w-25">
                <?php endif; ?>
            </div>
            <div class="group-button">
                <input type="submit" name="submit" value="Hoàn thành">
                <input type="button" value="Thoát" onclick="showDiaryForm()">
            </div>
        </form>
    </div>
</main>
</body>
</html>
