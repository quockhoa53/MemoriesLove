<?php
    include("include/database.php");
    include("include/function.php");
    include("include/upload.php");

    session_start();
    $username = 'Khách';

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $id_tk = $_SESSION['user_id'];
        $email = $_SESSION['email'];
    } elseif (isset($_SESSION['facebook_name'])) {
        $username = $_SESSION['facebook_name'];
    }
    if ($id_tk) {
        $datacustomer = ThongTinKhachHang($conn, $id_tk);
    }
    $datayourlove = [];
    if ($id_tk) {
        $datayourlove = TimNguoiHenHo($conn, $email);
    }
    $id_game = 1;
    if (isset($datayourlove['ID_KH'])) {
        $cauhoi = DanhSachCauHoi($conn, 1, $datayourlove['ID_KH']);
        $socauhoi = count($cauhoi);
    }
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['selectedAnswers'])) {
            $jsonData = $_POST['selectedAnswers'];
            $ketquatraloi = json_decode($jsonData, true);
        
            if (json_last_error() !== JSON_ERROR_NONE) {
                return;
            } else {
                $count = 0;
                foreach ($ketquatraloi as $id_ch => $id_ctl) {
                    $sql = "SELECT DAPAN FROM DANHSACHCAUTRALOI WHERE ID_CTL = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_ctl);
                    $stmt->execute();
                    $stmt->bind_result($dapAn);
                    $stmt->fetch();
                    $stmt->close();
        
                    if ($dapAn == 1) {
                        $count++;
                    }
                }
                //Thêm thành tích
                $diemthanhtich = ($count / $socauhoi) * 10;
                $sql = "INSERT INTO THANHTICH (DIEM, SOCAUDUNG, ID_GAME, ID_KH) VALUE (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("iiii", $diemthanhtich, $count, $id_game, $datacustomer['ID_KH']);
                    $stmt->execute();
                }
                $stmt->close();
                
                // Tính toán điểm
                $diem = ($diemthanhtich >= 7) ? 10 : -10;
                // Cập nhật điểm tình yêu
                $sql1 = "UPDATE KHACHHANG SET DIEMTINHYEU = DIEMTINHYEU + ? WHERE ID_KH = ?";
                $stmt1 = $conn->prepare($sql1);
                if ($stmt1) {
                    $stmt1->bind_param('ii', $diem, $datacustomer['ID_KH']);
                    $stmt1->execute();
                }
                $stmt1->close();
                // Redirect to avoid resubmission
                header("Location: game.php");
                exit;
            }
        }
        
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Page</title>
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6f0e5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 800px;
            text-align: center;
            margin-bottom: 20px;
            display: none;
        }
        .header {
            background-color: #4caf50;
            border-radius: 10px 10px 0 0;
            color: white;
            padding: 10px;
            font-size: 18px;
        }
        .option {
            margin: 15px 0;
            display: flex;
            align-items: center;
            border: 2px solid #4caf50;
            border-radius: 25px;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .option:hover {
            background-color: #f0f0f0;
        }
        .option.selected {
            background-color: #4caf50;
            color: white;
        }
        .navigation {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .nav-button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .nav-button:hover {
            background-color: #45a049;
        }
        h1 {
            color: #4caf50;
            margin-bottom: 20px;
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
        }

        .close-button:hover {
            background-color: rgba(255, 0, 0, 0.7);
        }
        input[type="submit"] {
            background-color: #0975bd;
            color: white;
            padding: 15px 30px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #082c87;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('closeBtn').onclick = function() {
                var result = confirm("Bạn có chắc chắn muốn thoát trò chơi khi chưa hoàn thành câu hỏi?");
                if (result) {
                    window.location.href = "game.php";
                }
            };
        });
    </script> 
</head>
<body>
    <button id="closeBtn" class="close-button">X</button>
    <div class="content-wrapper">
        <h1><b>YÊU NHAU THÌ HÃY HIỂU NHAU</b></h1>
        <?php
            if (isset($datayourlove['ID_KH'])) {
                $cauhoi = DanhSachCauHoi($conn, 1, $datayourlove['ID_KH']);
            } else {
                $cauhoi = null;
            }
            $count = 1;
            if ($cauhoi !== null && count($cauhoi) > 0) {
                foreach ($cauhoi as $q) {
                    if (is_array($q) && isset($q['ID_CH'], $q['NOIDUNG'])) {
                        echo '<div class="container" data-question-id="' . htmlspecialchars($q['ID_CH']) . '">';
                        echo '<div class="header"> Câu ' . htmlspecialchars($count) . ': ' . htmlspecialchars($q['NOIDUNG']) . '</div>';
                    
                        $cautraloi = DanhSachCauTraLoi($conn, $q['ID_CH']);
                        
                        if ($cautraloi !== null && is_array($cautraloi) && count($cautraloi) > 0) {
                            foreach ($cautraloi as $tl) {
                                if (is_array($tl) && isset($tl['ID_CTL'], $tl['NOIDUNG'])) {
                                    echo '<div class="option" onclick="selectOption(this)" data-answer-id="' . htmlspecialchars($tl['ID_CTL']) . '"><span>' . htmlspecialchars($tl['NOIDUNG']) . '</span></div>';
                                }
                            }
                        }
                        echo '<div class="navigation">';
                        echo '<button class="nav-button" onclick="goBack()">←</button>';
                        echo '<button class="nav-button" onclick="goNext()">→</button>';
                        echo '</div>';
                        echo '</div>';
                        $count++;
                    } else {
                        echo '<p>Người yêu bạn chưa có câu hỏi nào cho bạn.</p>';
                    }
                }
            }
        ?>
        <form id="quizForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="selectedAnswers" id="selectedAnswers">
            <input type="submit" name="submit" value="Xác nhận" onclick="showGame()">
        </form>

    </div>
    <script>
        let currentIndex = 0;
        const containers = document.querySelectorAll('.container');
        const selectedAnswers = {};

        function showQuestion(index) {
            containers.forEach((container, i) => {
                container.style.display = (i === index) ? 'block' : 'none';
                if (i === index && selectedAnswers[container.dataset.questionId]) {
                    const selectedOption = container.querySelector(`[data-answer-id="${selectedAnswers[container.dataset.questionId]}"]`);
                    if (selectedOption) {
                        selectedOption.classList.add('selected');
                    }
                }
            });
        }

        function selectOption(option) {
            const currentContainer = containers[currentIndex];
            const options = currentContainer.querySelectorAll('.option');
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            
            // Lưu lại ID của đáp án đã chọn
            const questionId = currentContainer.dataset.questionId;
            const answerId = option.dataset.answerId;
            selectedAnswers[questionId] = answerId;
        }

        function goBack() {
            if (currentIndex > 0) {
                currentIndex--;
                showQuestion(currentIndex);
            }
        }

        function goNext() {
            if (currentIndex < containers.length - 1) {
                currentIndex++;
                showQuestion(currentIndex);
            } else {
                alert("Đã hết câu hỏi.");
            }
        }

        showQuestion(currentIndex);

        document.getElementById('quizForm').onsubmit = function() {
            const answers = JSON.stringify(selectedAnswers);
            console.log(answers);
            document.getElementById('selectedAnswers').value = answers;
        };
    </script>
</body>
</html>
