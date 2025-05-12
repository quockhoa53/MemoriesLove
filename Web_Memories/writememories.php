<?php
ob_start();
include("include/database.php");
include("include/function.php");

session_start();
$username = 'Khách';
$id_tk = null;
$email = null;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $id_tk = $_SESSION['user_id'];
    $email = $_SESSION['email'];
} elseif (isset($_SESSION['facebook_name'])) {
    $username = $_SESSION['facebook_name'];
}
if(isset($_SESSION['thang']) && isset($_SESSION['nam'])){
    $thang = $_SESSION['thang'];
    $nam = $_SESSION['nam'];
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
    <link rel="stylesheet" href="/web_memories/css/diary.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JavaScript -->
    <script>
        $(document).ready(function() {
        $('.close-button').click(function() {
            const imageId = $(this).data('image-id');
            const imageContainer = $(this).closest('.image-container');

            $.ajax({
                url: 'writememories.php',
                type: 'POST',
                data: { id_img: imageId },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        imageContainer.remove();
                    } else {
                        alert('Lỗi xóa ảnh: ' + result.message);
                    }
                },
                error: function() {
                    alert('Lỗi xóa ảnh!');
                }
            });
        });
    });
    </script>
    <script>
        function showMemoriesForm() {
            window.location.href = 'memories.php';
        }
        $(document).ready(function() {
            $('.navbar-nav li').click(function() {
                $('.navbar-nav li').removeClass('active');
                $(this).addClass('active');
            });

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
    <script>
        $(document).ready(function() {
        $('.close-button').click(function() {
            const imageId = $(this).data('image-id');
            const imageContainer = $(this).closest('.image-container');

            // Thêm ID ảnh vào input ẩn
            let deleteImageIds = $('#delete_image_ids').val();
            deleteImageIds = deleteImageIds ? deleteImageIds.split(',') : [];
            deleteImageIds.push(imageId);
            $('#delete_image_ids').val(deleteImageIds.join(','));

            // Ẩn ảnh khỏi giao diện
            imageContainer.hide();
        });
    });
    </script>
    <script>
        function updateFileNamesAndPreview() {
            const input = document.getElementById('profilePic');
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = ''; // Xóa ảnh cũ trước khi thêm mới

            if (input.files.length > 0) {
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgContainer = document.createElement('div');
                            imgContainer.classList.add('img-container');

                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.classList.add('w-50');

                            const closeButton = document.createElement('span');
                            closeButton.innerHTML = '&times;';
                            closeButton.classList.add('close-button');
                            closeButton.onclick = function() {
                                imgContainer.style.display = 'none';
                            };

                            imgContainer.appendChild(closeButton);
                            imgContainer.appendChild(img);
                            imagePreview.appendChild(imgContainer);
                        };
                        reader.readAsDataURL(file);
                    }
                }
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

        $tieude = $noidung = $anh = "";
        $uploadedFiles = [];
        $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        $memoriesId = isset($_GET['memories_id']) ? $_GET['memories_id'] : '';
        $memoriesData = [];
        if ($memoriesId) {
            $memoriesData = LayKiNiemTheoID($conn, $memoriesId);
        }
        $images = DanhSachAnhKiNiem($conn, $memoriesId);
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id_kn = isset($_POST['id_kn']) ? $_POST['id_kn'] : '';
            $memoriesDataTemp = [];
            if ($id_kn) {
                $memoriesDataTemp = LayKiNiemTheoID($conn, $id_kn);
            }
            $imagesTmp = DanhSachAnhKiNiem($conn, $id_kn);
            if (isset($_POST['delete_image_ids']) && !empty($_POST['delete_image_ids'])) {
                $deleteImageIds = explode(',', $_POST['delete_image_ids']);
                foreach ($deleteImageIds as $id_anh) {
                    $sql = "UPDATE ANHKINIEM SET TRANGTHAIXOA = 1 WHERE ID_ANH = ?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("i", $id_anh);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
            $tieude = filter_input(INPUT_POST, "title", FILTER_SANITIZE_SPECIAL_CHARS);
            $noidung = $_POST['content'];

            // Xử lý upload ảnh
            if (isset($_FILES['profilePic']) && !empty($_FILES['profilePic']['name'][0])) {
                $uploadDir = 'upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileCount = count($_FILES['profilePic']['name']);

                for ($i = 0; $i < $fileCount; $i++) {
                    $uploadFile = $uploadDir . basename($_FILES['profilePic']['name'][$i]);

                    if (move_uploaded_file($_FILES['profilePic']['tmp_name'][$i], $uploadFile)) {
                        $uploadedFiles[] = $uploadFile;
                    } else {
                        echo "Không thể tải lên ảnh: " . htmlspecialchars($_FILES['profilePic']['name'][$i]);
                    }
                }
            } else {
                $uploadedFiles = $imagesTmp;
            }

            if(($_POST['id_kn']) && !empty($_POST['id_kn'])){
                $sql = "UPDATE KINIEM SET TENKINIEM = ?, NGAYKINIEM = ?, MOTA = ? WHERE ID_KN = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssi", $tieude, $date, $noidung, $id_kn);
                    $stmt->execute();
                    $stmt->close();
                }
                //Thêm ảnh
                ThemAnhKiNiem($conn, $uploadedFiles, $id_kn);
                // Redirect to avoid resubmission
                header("Location: memories.php?month=$thang&year=$nam");
                exit();
            }
            else{
                //Thêm kỉ niệm
                $sql = "CALL SP_THEMKINIEM(?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("iisss", $datacustomer['ID_KH'], $datayourlove['ID_KH'], $tieude, $date, $noidung);
                    $stmt->execute();
                    $stmt->close();
                }
                $listmemories = LayIDKiNiem($conn);
                //Thêm ảnh
                ThemAnhKiNiem($conn, $uploadedFiles, $listmemories['ID_KN']);
                // Redirect to avoid resubmission
                header("Location: memories.php");
                exit();
            }
        }

        $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
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
        <input type="hidden" name="id_kn" value="<?php echo htmlspecialchars($memoriesId); ?>">
        <input type="hidden" id="delete_image_ids" name="delete_image_ids" value="">
        <div class="ngang">
            <label for="date"><b>Kỉ niệm ngày:</b></label>
            <input type="date" id="date" name="date" class="date" value="<?php echo isset($memoriesData['NGAYKINIEM']) ? htmlspecialchars($memoriesData['NGAYKINIEM']) : htmlspecialchars($date); ?>">
        </div>
        <div class="form-group">
                <label for="title">Tiêu đề:</label>
                <input type="title" id="title" name="title" value="<?php echo isset($memoriesData['TENKINIEM']) ? htmlspecialchars($memoriesData['TENKINIEM']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="content">Nội dung:</label>
            <textarea id="content" name="content" placeholder="Hôm nay có gì vui không?..."><?php echo isset($memoriesData['MOTA']) ? htmlspecialchars($memoriesData['MOTA']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="profilePic">Ảnh kỉ niệm:</label>
            <div class="custom-file-upload">
                <label for="profilePic" id="fileLabel">Tải ảnh lên</label>
                <input type="file" id="profilePic" name="profilePic[]" accept="image/*" multiple onchange="updateFileNamesAndPreview()">
            </div>
        </div>
        <div id="imagePreview" class="image-previews"></div> <!-- Khu vực xem trước ảnh -->
        <div id="image-gallery" class="image-gallery">
            <?php
            if (!empty($images)) {
                foreach ($images as $img) {
                    echo '<div class="image-container">';
                    echo '<img src="' . htmlspecialchars($img['TENANH']) . '" alt="Ảnh kỉ niệm" class="w-50">';
                    echo '<button type="button" class="close-button" data-image-id="' . htmlspecialchars($img['ID_ANH']) . '">X</button>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <div class="group-button">
            <input type="submit" name="submit" value="Xác nhận">
            <input type="button" value="Thoát" onclick="showMemoriesForm()">
        </div>
    </form>
    </div>
</main>
</body>
</html>
<?php
ob_end_flush();
?>
