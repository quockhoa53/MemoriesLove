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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/game.css">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <?php
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
            header("Location: game.php");
            exit();
         }
    ?>
</head>
<body>
<header>
<section id="top">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center text-center">
            <div class="col-md-3">
                <a href="index.php">
                    <img src="/web_memories/images/logo_memorieslove.gif" class="w-50 ml-auto" alt="Logo Memories Love">
                </a> 
            </div>
            <div class="col-md-6">
                <form action="search.php" method="GET">
                    <input type="text" name="query" class="form-control" placeholder="Tìm..." aria-label="Search" required>
                </form>
            </div>
            <div class="col-md-3 d-flex justify-content-end align-items-center">
                <a href="account.php"><i class="bi bi-person-hearts menu-toggle"></i></a>
                <span><?php echo htmlspecialchars($username); ?></span>
            </div>
        </div>
    </div>
</section>
<hr>
<section id="head">
    <div class="container">
        <nav class="navbar navbar-expand-md navbar-light justify-content-center">
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item <?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="index.php"><b>Trang chủ</b> <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item <?php echo ($currentPage == 'diary.php') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="diary.php"><b>Nhật ký</b> </a>
                    </li>
                    <li class="nav-item <?php echo ($currentPage == 'memories.php') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="memories.php"><b>Kỉ niệm</b> </a>
                    </li>
                    <li class="nav-item <?php echo ($currentPage == 'game.php') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="game.php"><b>Game</b></a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</section>
</header>
<main>
    <div class="container">
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
