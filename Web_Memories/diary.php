<?php
    include("include/database.php");
    include("include/function.php");

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

    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Lấy dữ liệu khách hàng từ cơ sở dữ liệu
    $datacustomer = [];
    if ($id_tk) {
        $datacustomer = ThongTinKhachHang($conn, $id_tk);
    }
    
    $id_kh = isset($datacustomer['ID_KH']) ? $datacustomer['ID_KH'] : null;
    $datadiary = DanhSachNhatKyTheoNgay($conn, $id_kh, $startDate, $endDate);
    
    $currentPage = basename($_SERVER['PHP_SELF']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['diary_id'])){
        $id_nk = $_POST['diary_id'];
        $sql = "UPDATE NHATKY SET TRANGTHAIXOA = 1 WHERE ID_NK = ?";
        $stmt = $conn->prepare($sql);
        if($stmt){
            $stmt->bind_param("i", $id_nk);
            $stmt->execute();
        }
        $stmt->close();
        // Chuyển hướng sau khi xử lý biểu mẫu
        header("Location: " . $_SERVER['REQUEST_URI']);
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
    <link rel="stylesheet" href="/web_memories/css/diary.css">  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showwritediaryForm() {
            window.location.href = 'writediary.php';
        }

        $(document).ready(function() {
            $('.navbar-nav li').click(function() {
                $('.navbar-nav li').removeClass('active');
                $(this).addClass('active');
            });
        });

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
<main>
    <div class="container">
        <?php if ($query){ ?>
            <h2>Kết quả tìm kiếm cho <i>"<?php echo htmlspecialchars($query); ?>"</i></h2>
            <div id="result" class="search-container">
                <?php
                    include("include/search.php");
                ?>
            </div>
        <?php }else{?>
        <div class="group">
            <div class="groud-ngang">
                <img src="/web_memories/images/thienthantinhyeu.gif" class="w-25">
                <span><p>Hôm nay của bạn thế nào?</p></span>
            </div>
            <span><input type="button" id="btn-pink" value="Viết nhật ký" onclick="showwritediaryForm()"></span>
        </div>
        <hr>
        <div class="group">
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="groud-ngang"></div>
                <label for="start_date">Từ ngày:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                <label for="end_date">Đến ngày:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>"><br><br>
                <input type="submit" id="btn-blue" value="Lọc nhật ký" class="button-loc">
            </form>
        </div>
        <hr>
        <div class="group">
            <?php
                if ($datadiary !== null && count($datadiary) > 0) {
                    foreach ($datadiary as $diary) {
                        echo "<div class='post'>";
                        echo "<div class='post-header'>";
                        echo "<h2 class='post-title'>" . htmlspecialchars($diary['TIEUDE']) . "</h2>";
                        echo "<div class='post-buttons'>";
                        echo '<form method="GET" action="writediary.php">';
                        echo '<input type="hidden" name="diary_id" value="' . htmlspecialchars($diary['ID_NK']) . '">';
                        echo '<button type="submit" name="edit" class="edit-btn">
                                    <i class="bi bi-pencil-square"></i>
                            </button>';
                        echo '</form>';
                        echo '<form method="POST" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'">';
                        echo '<input type="hidden" name="diary_id" value="' . htmlspecialchars($diary['ID_NK']) . '">';
                        echo '<button type="button" class="delete-btn" data-id="' . htmlspecialchars($diary['ID_NK']) . '" data-toggle="modal" data-target="#confirmDeleteModal">
                              <i class="bi bi-trash"></i>
                              </button>';
                        echo '</form>';
                        echo "</div>";
                        echo "</div>";
                        echo "<p class='post-content'>" . nl2br(htmlspecialchars($diary['NOIDUNG'])) . "</p>";
                        echo "<p class='post-date'><strong>Ngày viết:</strong> " . htmlspecialchars($diary['NGAYVIET']) . "</p>";
                        if (!empty($diary['ANH_NK'])) {
                            echo "<div class='post-image'><img src='" . htmlspecialchars($diary['ANH_NK']) . "' alt='Diary Image' class='w-50'></div>";
                        }
                        echo "<hr>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Bạn chưa có nhật ký nào được viết</p>";
                }
            ?>
        </div>
        <?php } ?>
    </div>
    <div id="lightbox" class="lightbox">
        <span class="close">&times;</span>
        <img id="lightbox-image" src="" alt="Expanded view">
    </div>
    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Xóa nhật ký?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa bài nhật ký này?
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
            var diaryIdToDelete;

            $('.delete-btn').click(function() {
                diaryIdToDelete = $(this).data('id');
            });

            $('#confirmDeleteButton').click(function() {
                if (diaryIdToDelete) {
                    $('<form>', {
                        'method': 'POST',
                        'action': '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>'
                    }).append($('<input>', {
                        'type': 'hidden',
                        'name': 'diary_id',
                        'value': diaryIdToDelete
                    })).appendTo('body').submit();
                }
            });
        });
    </script>

    <script>
        // Xử lý ảnh click để mở lightbox
        document.querySelectorAll('.post-image img').forEach(image => {
            image.addEventListener('click', function() {
                const lightbox = document.getElementById('lightbox');
                const lightboxImage = document.getElementById('lightbox-image');
                lightboxImage.src = this.src; // Lấy URL ảnh
                lightbox.style.display = 'flex';
            });
        });

        // Đóng lightbox khi nhấp vào dấu x
        document.querySelector('.lightbox .close').addEventListener('click', function() {
            document.getElementById('lightbox').style.display = 'none';
        });

        // Đóng lightbox khi nhấp bên ngoài ảnh
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
</main>
</body>
</html>
