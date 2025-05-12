<?php
include("include/database.php");
include("include/function.php");

session_start();
$username = 'Khách';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $id_tk = $_SESSION['user_id'];
    $email = $_SESSION['email'];
} elseif (isset($_SESSION['facebook_name'])) {
    $username = $_SESSION['facebook_name'];
}

// Lấy dữ liệu khách hàng từ cơ sở dữ liệu
$datayourlove = [];
if ($id_tk) {
    $datacustomer = ThongTinKhachHang($conn, $id_tk);
}
// Lấy dữ liệu khách hàng từ cơ sở dữ liệu
$datayourlove = [];
if ($id_tk) {
    $datacustomer = ThongTinKhachHang($conn, $id_tk);
}

$sql = "CALL SP_TACA_ANHKINIEM(?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $datacustomer['ID_KH'], $datacustomer['ID_KH']);
$stmt->execute();
$result = $stmt->get_result();
$viewimages = [];
if ($result == false) {
    echo "Không tìm thấy ảnh kỉ niệm của người dùng này!";
} elseif ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $viewimages[] = [
            'ID_KN' => $row['ID_KN'],
            'ID_ANH' => $row['ID_ANH'],
            'TENANH' => $row['TENANH']
        ];
    }
} else {
    $viewimages = null;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love - Xem ảnh</title>
    <style>
        /* Đặt lại các phần CSS bạn đã có */
        html,
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff7b89, #ffb199);
            font-family: "Arial", sans-serif;
            min-height: 100vh;
        }

        .gallery-container {
            position: relative;
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            box-sizing: border-box;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            overflow: hidden;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
            overflow-y: auto;
        }

        .gallery img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.5s ease, transform 0.5s ease;
            cursor: pointer;
        }

        .gallery img.show {
            opacity: 1;
            transform: scale(1);
        }

        .gallery img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        /* Lightbox CSS */
        .lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 10px;
        }

        .close-lightbox {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(255, 0, 0, 0.7);
            border: none;
            color: white;
            padding: 10px 15px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .close-lightbox:hover {
            background-color: rgba(255, 0, 0, 1);
        }

        .close-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(255, 0, 0, 0.5);
            border: none;
            color: white;
            padding: 10px 15px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 50%;
            transition: background-color 0.3s;
            z-index: 1000;
        }

        .close-button:hover {
            background-color: rgba(255, 0, 0, 0.7);
        }
    </style>
    <script>
        function showMemoriesForm() {
            window.location.href = 'memories.php';
        }

        window.onload = function() {
            const images = document.querySelectorAll('.gallery img');
            images.forEach((img, index) => {
                setTimeout(() => {
                    img.classList.add('show');
                }, index * 300); // delay tăng dần cho từng ảnh (300ms cho mỗi ảnh)
            });

            // Tạo sự kiện click cho mỗi ảnh trong gallery
            images.forEach(image => {
                image.addEventListener('click', () => {
                    openLightbox(image.src); // Mở lightbox với ảnh đã chọn
                });
            });
        };

        // Mở lightbox
        function openLightbox(imageSrc) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImg = lightbox.querySelector('img');
            const closeBtn = document.getElementById('closeBtn'); // Nút "X" đóng trang chính

            lightbox.style.display = 'flex';
            lightboxImg.src = imageSrc;

            // Ẩn nút đóng trang chính
            closeBtn.style.display = 'none';
        }

        // Đóng lightbox khi click vào nền hoặc nút đóng
        function closeLightbox(event) {
            event.stopPropagation(); // Ngăn sự kiện bắn ra ngoài khi click vào nút "X" trong lightbox
            document.getElementById('lightbox').style.display = 'none';

            // Hiển thị lại nút đóng trang chính
            document.getElementById('closeBtn').style.display = 'block';
        }
    </script>
</head>

<body>
    <button id="closeBtn" class="close-button" onclick="showMemoriesForm()">X</button>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox(event)">
        <img src="" alt="Lightbox Image">
        <button class="close-lightbox" onclick="closeLightbox(event)">X</button>
    </div>

    <div class="gallery-container">
        <h2 style="font-size: 2.5rem; font-weight: bold; color: #ff6b6b;">Khoảnh Khắc Yêu Thương</h2>
        <div class="gallery">
            <?php
            if ($viewimages != null) {
                foreach ($viewimages as $img) {
                    echo '<img src="' . $img['TENANH'] . '" alt="Ảnh kỉ niệm">';
                }
            }
            ?>
        </div>
    </div>
</body>

</html>