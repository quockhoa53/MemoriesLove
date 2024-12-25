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
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($id_tk) {
        $datacustomer = ThongTinKhachHang($conn, $id_tk);
    }
    if ($id_tk) {
        $datayourlove = TimNguoiHenHo($conn, $email);
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question_id'])){
        $id_ch = $_POST['question_id'];
        $sql = "CALL SP_XOACAUHOIGAME(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_ch);
        $stmt->execute();
    }
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript -->
    <script>
        function PlayGameQuestion() {
            window.location.href = 'questiongame.php';
        }
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

            document.getElementById(`huycauhoi`).onclick = function(){
                questionForm.style.display = 'none';
            }
        });
    </script>
    <script>
        function showQuestion() {
            document.getElementById('listquestion-form').classList.remove('hidden');
            document.getElementById('result-form').classList.add('hidden');
        }

        function showResult() {
            document.getElementById('result-form').classList.remove('hidden');
            document.getElementById('listquestion-form').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            showQuestion();
        });
    </script>
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
<main>
    <div class="container">
        <?php if ($query){ ?>
            <h2>Kết quả tìm kiếm cho <i>"<?php echo htmlspecialchars($query); ?>"</i></h2>
            <div id="result" class="search-container">
                <?php
                    include("include/search.php");
                ?>
            </div>
        <?php } else { ?>
        <?php
            if(!empty($datacustomer['ID_KH'])){
                if(CheckTrangThaiHenHo($conn, $datacustomer['ID_KH']) == 1){
        ?>
        <div class="game-container">
            <div class="text">
                <div class="group-container">
                    <h5>Trò chơi: </h5>
                    <p>Bạn hiểu người ấy bao nhiêu?</p>
                    <!-- Form nhập số câu hỏi -->
                    <div id="questionForm" style="display: none;">
                        <form method="post" action="cauhoi.php">
                            <div class="form-group">
                                <label for="numQuestions">Nhập số câu hỏi:</label>
                                <input type="number" id="numQuestions" name="numQuestions" class="form-control" min="1" required>
                            </div>
                            <button type="submit" class="btn-primary">Gửi</button>
                            <button type="submit" id="huycauhoi" class="btn-secondary">Hủy</button>
                        </form>
                    </div>
                </div>
                <br>
                <form class="question-form" method="post" action="questiongame.php">
                    <button type="button" id="question" name="question" class="question-btn">Thêm câu hỏi</button>
                    <button type="submit" id="start" name="start" class="start-btn">Bắt đầu chơi</button>
                </form>
                <!-- Form câu hỏi và thành tích gần đây -->
                 <div class="group-container">
                    <div class="form-toggle">
                        <button id="questionBtn" onclick="showQuestion()">Danh sách câu hỏi</button>
                        <button id="resultBtn" onclick="showResult()">Thành tích gần đây</button>
                    </div>
                 </div>
                 <div class="form-container">
                    <!-- Form Danh sách câu hỏi -->
                    <form id="listquestion-form" class="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <h5 class="ngang"><b>CÂU HỎI CỦA BẠN</b></h5>
                        <br>
                        <?php
                            if (isset($datacustomer['ID_KH'])) {
                                $cauhoi = DanhSachCauHoi($conn, 1, $datacustomer['ID_KH']);
                            } else {
                                $cauhoi = null;
                            }
                            $count = 1;
                            if ($cauhoi !== null && count($cauhoi) > 0){
                                foreach($cauhoi as $q){
                                    if (is_array($q) && isset($q['ID_CH'], $q['NOIDUNG'])){
                                        echo '<div class="container" data-question-id="'.$q['ID_CH'].'">';
                                        echo '<div class="header"> Câu '.htmlspecialchars($count).': '.htmlspecialchars($q['NOIDUNG']).'</div>';
                                        
                                        $cautraloi = DanhSachCauTraLoi($conn, $q['ID_CH']);
                                        if ($cautraloi !== null && is_array($cautraloi) && count($cautraloi) > 0){
                                            foreach($cautraloi as $tl){
                                                echo '<div class="option" onclick="selectOption(this)" data-answer-id="'.$tl['ID_CTL'].'"><span>'.htmlspecialchars($tl['NOIDUNG']).'</span></div>';
                                            }
                                        }
                                        $answer = LayDapAnDung($conn, $q['ID_CH']);
                                        if ($answer !== null) {
                                            echo '<div><b>Đáp án:</b> '.htmlspecialchars($answer).'</div>';
                                        } else {
                                            echo '<div>Không có đáp án đúng!</div>';
                                        }
                                        echo '<br>';
                                        echo '</div>';
                                        echo '<input type="hidden" name="question_id" value="' . htmlspecialchars($q['ID_CH']) . '">';
                                        echo '<div>Xóa câu hỏi:<span><button type="button" class="delete-btn" data-id="' . htmlspecialchars($q['ID_CH']) . '" data-toggle="modal" data-target="#confirmDeleteModal">
                                              <i class="bi bi-trash"></i>
                                              </button></span></div>';
                                        echo '<hr>';
                                        $count++;
                                    }
                                }
                            }
                        ?>
                    </form>
                    <!-- Form Thành tích gần đây -->
                    <form id="result-form" class="form hidden" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                        <h5 class="ngang"><b>ĐIỂM TÌNH YÊU: <?php echo htmlspecialchars($datacustomer['DIEMTINHYEU']);?></b></h5>
                        <br>
                        <div class="leaderboard">
                            <table>
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên</th>
                                        <th>Số câu đúng</th>
                                        <th>Điểm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if (isset($datayourlove['ID_KH'])) {
                                        $cauhoi = DanhSachCauHoi($conn, 1, $datayourlove['ID_KH']);
                                        $socauhoilove = count($cauhoi);
                                    }
                                    if (isset($datacustomer['ID_KH'])) {
                                        $cauhoi = DanhSachCauHoi($conn, 1, $datacustomer['ID_KH']);
                                        $socauhoi = count($cauhoi);
                                    }
                                    if (isset($datayourlove['ID_KH']) && isset($datacustomer['ID_KH'])) {
                                        $thanhtich = DanhSachThanhTich($conn, 1, $datacustomer['ID_KH'], $datayourlove['ID_KH']);
                                    }
                                    if (isset($thanhtich) && count($thanhtich) > 0) {
                                        $index = 1;
                                        foreach ($thanhtich as $tt) {
                                            if($tt != null && count($tt) > 0){
                                                if ($index == 1) echo '<tr class="yellow-backgroud">';
                                                else echo '<tr>';
                                                echo '<td>' . htmlspecialchars($index) . '</td>';
                                                echo '<td>' . htmlspecialchars($tt['HOTEN']) . '</td>';
                                                echo '<td>' . htmlspecialchars($tt['SOCAUDUNG']) .'</td>';
                                                echo '<td>' . htmlspecialchars($tt['DIEM']) .'</td>';
                                                echo '</tr>';
                                                $index++;
                                            }
                                            if($index == 10) break;
                                        }
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
            }
            else{
                echo '<br>';
                echo '<div class="group-container game-text">Bạn hãy tìm đối tượng hẹn hò để sử dụng tính năng này nhé!</div>';
            }
        }
        else{
            echo '<br>';
            echo '<div class="group-container game-text">Bạn hãy tìm đối tượng hẹn hò để sử dụng tính năng này nhé!</div>';
        }
        ?>
        <?php } ?>
    </div>
</main>
<!-- Modal Xác nhận xóa -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa câu hỏi này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" id="confirmDeleteButton" class="btn-danger">Xóa</button>
            </div>
        </div>
    </div>
</div>
<script>
        $(document).ready(function() {
            var questionIdToDelete;

            $('.delete-btn').click(function() {
                questionIdToDelete = $(this).data('id');
            });

            $('#confirmDeleteButton').click(function() {
                if (questionIdToDelete) {
                    $('<form>', {
                        'method': 'POST',
                        'action': '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>'
                    }).append($('<input>', {
                        'type': 'hidden',
                        'name': 'question_id',
                        'value': questionIdToDelete
                    })).appendTo('body').submit();
                }
            });
        });
    </script>
</body>
</html>
