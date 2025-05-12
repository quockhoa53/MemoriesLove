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
    $currentPage = basename($_SERVER['PHP_SELF']);

    // Xử lý lưu câu hỏi
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['questions'])){
        $game_id = 1;
        $questions = $_POST['questions'];
        foreach ($questions as $index => $question) {
            $cauhoi = $question['question'];
            $dapan = $question['correct'];
            $sql = "INSERT INTO CAUHOI(NOIDUNG, ID_GAME, ID_KH) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if($stmt){
                $stmt->bind_param("sii", $cauhoi, $game_id, $datacustomer['ID_KH']);
                $stmt->execute();
            }

            $id_cauhoi = $stmt->insert_id;
            foreach ($question['options'] as $option){
                $sql1 = "INSERT INTO DANHSACHCAUTRALOI(NOIDUNG, DAPAN, ID_CH) VALUES (?, ?, ?)";
                $stmt1 = $conn->prepare($sql1);
                if($stmt1){
                    $temp = ($dapan == $option) ? 1 : 0;
                    $stmt1->bind_param("sii", $option, $temp, $id_cauhoi);
                    $stmt1->execute();
                }
            }
        }
        $stmt->close();
        $stmt1->close();
        // Chuyển hướng sau khi đã xử lý xong
        header("Location: game.php");
        exit();
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
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 12px;
            background: linear-gradient(145deg, #ffe6f0, #ffccdd);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-family: 'Arial', sans-serif;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            margin-bottom: 5px;
            color: #d63384;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ff8aa8;
            border-radius: 8px;
            font-size: 14px;
            background-color: #fff0f5;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #e83e8c;
            box-shadow: 0 0 5px rgba(232, 62, 140, 0.5);
        }
        .question-container {
            margin-bottom: 20px;
            border-bottom: 1px solid #ffc2d1;
            padding-bottom: 10px;
            padding-top: 10px;
        }
        .question-container:last-child {
            border-bottom: none;
        }
        .add-option-button {
            background-color: #e83e8c;
            color: white;
            border: none;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 5px;
        }
        .add-option-button:hover {
            background-color: #c70075;
        }
        .question-btn {
            display: block;
            background-color: #ff66a3;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 30px;
            cursor: pointer;
            margin: 20px auto;
            transition: all 0.3s ease;
        }
        .question-btn:hover {
            background-color: #e83e8c;
            box-shadow: 0 4px 8px rgba(232, 62, 140, 0.4);
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const verticalMenu = document.querySelector('.vertical-menu');
            const questionButton = document.querySelector('#question');
            const questionForm = document.querySelector('#questionForm');

            menuToggle.addEventListener('click', function() {
                if (verticalMenu.style.display === 'block') {
                    verticalMenu.style.display = 'none';
                } else {
                    verticalMenu.style.display = 'block';
                }
            });

            questionButton.addEventListener('click', function(event) {
                event.preventDefault();
                if (questionForm.style.display === 'block') {
                    questionForm.style.display = 'none';
                } else {
                    questionForm.style.display = 'block';
                }
            });
        });
    </script>
    <script>
        function addOption(questionIndex) {
            var optionsContainer = document.getElementById('options_' + questionIndex);
            var optionCount = optionsContainer.querySelectorAll('input[type="text"]').length;
            var newOption = document.createElement('div');
            newOption.innerHTML = `
                <input type="text" name="questions[${questionIndex}][options][]" placeholder="Câu trả lời ${optionCount + 1}">
            `;
            optionsContainer.appendChild(newOption);
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
    <div class="form-container">
        <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $socau = $_POST['numQuestions'];
                echo '<form method="POST" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'">';
                for ($i = 0; $i < $socau; $i++) {
                    echo '<div class="question-container">';
                    echo '<label>Câu ' . ($i + 1) . ':</label>';
                    echo '<input type="text" name="questions[' . $i . '][question]" required><br>';
                    echo '<div id="options_' . $i . '">';
                    echo '<input type="text" name="questions[' . $i . '][options][]" placeholder="Câu trả lời 1" required><br>';
                    echo '</div>';
                    echo '<button type="button" class="add-option-button" onclick="addOption(' . $i . ')">Add Option</button><br>';
                    echo '<label>Đáp án:</label>';
                    echo '<input type="text" name="questions[' . $i . '][correct]" required><br>';
                    echo '</div>';
                }
                echo '<button type="submit" class="question-btn">Lưu câu hỏi</button>';
                echo '</form>';
            }
        ?>
    </div>
</main>
</body>
</html>
