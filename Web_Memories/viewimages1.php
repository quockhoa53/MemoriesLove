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
        while($row = $result->fetch_assoc()){
            $viewimages[] = [
                'ID_KN' => $row['ID_KN'],
                'ID_ANH' => $row['ID_ANH'],
                'TENANH' => $row['TENANH']
            ];
        }
    } else {
        $viewimages= null;
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
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4e1f2;
        }

        .gallery-container {
            position: relative;
            width: 100%;
            max-width: 90%;
            padding: 10px;
            box-sizing: border-box;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            max-width: 100%;
            overflow: auto;
        }

        .gallery img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            animation: move 5s infinite;
        }

        .close-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 0, 0, 0.5);
            border: none;
            color: white;
            padding: 5px 10px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            z-index: 1000;
        }

        .close-button:hover {
            background-color: rgba(255, 0, 0, 0.7);
        }

        @keyframes move {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-50px);
            }

            100% {
                transform: translateY(0);
            }
        }
    </style>
    <script>
        function showMemoriesForm() {
            window.location.href = 'memories.php';
        }
    </script>
</head>
<body>
    <button id="closeBtn" class="close-button" onclick="showMemoriesForm()">X</button>
    <div class="gallery-container">
        <div class="gallery">
            <?php
                if ($viewimages != null) {
                    foreach ($viewimages as $img) {
                        echo '<img src="'.$img['TENANH'].'" alt="Ảnh kỉ niệm">';
                    }
                }
            ?>
        </div>
    </div>
</body>
</html>
