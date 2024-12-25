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
    
    if(isset($_SESSION['thang']) && isset($_SESSION['nam'])){
        $thang = $_SESSION['thang'];
        $nam = $_SESSION['nam'];
    }

    // Lấy dữ liệu khách hàng từ cơ sở dữ liệu
    $datacustomer = ThongTinKhachHang($conn, $id_tk);

    // Lấy danh sách nhật ký theo ngày
    $id_kn = isset($_GET['id_kn']) ? intval($_GET['id_kn']) : 0;

    // Lấy danh sách ảnh từ cơ sở dữ liệu
    $images = DanhSachAnhKiNiem($conn, $id_kn);

    // Lấy chỉ số của ảnh được chọn
    $startIndex = isset($_GET['start_index']) ? intval($_GET['start_index']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ảnh Kỉ Niệm</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .container {
            position: relative;
            max-width: 80vw;
            max-height: 80vh;
            display: flex;
            align-items: center;
        }

        .nav-button {
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            padding: 10px;
            font-size: 24px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        #prevBtn {
            margin-right: 10px;
        }

        #nextBtn {
            margin-left: 10px;
        }

        .image-gallery {
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px solid #ddd;
            border-radius: 10px;
            background-color: white;
            padding: 20px;
            box-sizing: border-box;
        }

        .image-gallery img {
            max-width: 100%;
            max-height: 80vh;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <button id="prevBtn" class="nav-button" onclick="showPreviousImages()">&#60;</button>
        <div id="image-gallery" class="image-gallery">
            <!-- Ảnh sẽ được thêm ở đây -->
        </div>
        <button id="nextBtn" class="nav-button" onclick="showNextImages()">&#62;</button>
    </div>
    <script>
        let currentImageIndex = <?php echo $startIndex; ?>;
        const imagesPerPage = 1;
        let allImages = <?php echo json_encode($images); ?>;

        function displayImages() {
            const gallery = document.getElementById('image-gallery');
            gallery.innerHTML = '';

            const start = currentImageIndex;
            const end = Math.min(start + imagesPerPage, allImages.length);

            for (let i = start; i < end; i++) {
                const img = document.createElement('img');
                img.src = `${allImages[i].TENANH}`;
                img.alt = 'Ảnh kỉ niệm'; 
                gallery.appendChild(img);
            }

            updateNavigationButtons();
        }

        function updateNavigationButtons() {
            document.getElementById('prevBtn').disabled = currentImageIndex === 0;
            document.getElementById('nextBtn').disabled = currentImageIndex >= allImages.length - 1;
        }

        function showPreviousImages() {
            if (currentImageIndex > 0) {
                currentImageIndex--;
                displayImages();
            }
        }

        function showNextImages() {
            if (currentImageIndex < allImages.length - 1) {
                currentImageIndex++;
                displayImages();
            }
        }

        displayImages();
    </script>
</body>
</html>
