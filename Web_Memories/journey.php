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
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/journey.css">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript -->
    <script>
        $(document).ready(function() {
            $('.navbar-nav li').click(function() {
                $('.navbar-nav li').removeClass('active');
                $(this).addClass('active');
            });
        });
        document.getElementById('accountForm').addEventListener('submit', function() {
            this.querySelector('button[type="submit"]').disabled = true;
        });
    </script>
    <script>
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
    <script>
        $("#search").on("keypress", function(e) {
            if (e.which == 13) {
                e.preventDefault();
                let query = $(this).val();

                if (query.length > 0) {
                    $.ajax({
                        url: "index.php",
                        type: "GET",
                        data: { query: query },
                        success: function(data) {
                            window.location.href = "index.php?query=" + query;
                        }
                    });
                }
            }
        });
    </script>
    <script>
        function showJourneyManager() {
            window.location.href = 'journey-manager.php';
        }
    </script>
    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);

        $datayourlove = [];
        if ($id_tk) {
            $datacustomer = ThongTinKhachHang($conn, $id_tk);
        }
        $hotenban = explode(' ', !empty($datacustomer['HOTEN']) ? $datacustomer['HOTEN'] : null);
        $tenban = end($hotenban);

        $datayourlove = [];
        if ($id_tk) {
            $datayourlove = TimNguoiHenHo($conn, $email);
        }
        $hotenny = explode(' ', !empty($datayourlove['HOTEN']) ? $datayourlove['HOTEN'] : null);
        $tenny = end($hotenny);

        $datajourney = [];
        if($id_tk){
            $datajourney = DanhSachHanhTrinhYeu($conn, $datacustomer['ID_KH']);
        }
    ?>
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
<main class="main-content">
    <div class="container">
        <?php if ($query){ ?>
            <h2>Kết quả tìm kiếm cho <i>"<?php echo htmlspecialchars($query); ?>"</i></h2>
            <div id="result" class="search-container">
                <?php
                    include("include/search.php");
                ?>
            </div>
        <?php } else { ?>
            <header class="head-journey">
                <h1>Hành Trình Yêu Của <b><?php echo htmlspecialchars($tenban); ?></b> & <b><?php echo htmlspecialchars($tenny); ?></b></h1>
                <p>Mỗi khoảnh khắc bên nhau đều là một mảnh ghép tuyệt vời trong câu chuyện của chúng ta.</p>
                <p>Hãy ghi lại những cột mốc đáng nhớ của bạn và người ấy ❤</p>
                <button type="button" id="btn-pink" onclick="showJourneyManager()">Bắt đầu ngay</button>
            </header>
            <section class="timeline">
                <?php
                    if (!empty($datajourney)) {
                        foreach ($datajourney as $row) {
                            echo '<div class="timeline-item">';
                            echo '<img src="'.htmlspecialchars($row['ANH']).'" alt="Ngày đầu gặp gỡ">';
                            echo '<div class="timeline-content">';
                            echo '<h3>' . htmlspecialchars($row['TIEUDE']) . '</h3>';
                            echo '<p><b>Người viết: '.htmlspecialchars($row['NGUOIVIET']).'</b></p>';
                            echo '<p><b>Ngày: ' . htmlspecialchars($row['NGAY']) . '</b></p>';
                            echo '<p>' . htmlspecialchars($row['NOIDUNG']) . '</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }          
                ?>
            </section>

            <section class="counter">
                <h2><b>Chúng ta đã bên nhau</b></h2>
                <p class="title-love"><strong>1,825 ngày</strong></p>
            </section>

            <section class="gallery">
                <?php
                if (!empty($datajourney)) {
                    foreach ($datajourney as $row) {
                        echo '<img src="'.htmlspecialchars($row['ANH']).'" alt="Ảnh kỉ niệm">';
                    }
                }
                ?>
            </section>

            <footer>
                <p>Với tất cả tình yêu ❤️ <b><?php echo htmlspecialchars($tenban); ?> & <?php echo htmlspecialchars($tenny); ?></b></p>
            </footer>
            <br>
        <?php } ?>
    </div>
</main>
<script>
    document.addEventListener("DOMContentLoaded", () => {
  const timelineItems = document.querySelectorAll(".timeline-item");

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
        }
      });
    },
    { threshold: 0.1 }
  );

  timelineItems.forEach((item) => observer.observe(item));
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
    const header = document.querySelector(".head-journey");

    function createHeart() {
        const heart = document.createElement("div");
        heart.classList.add("heart");
        heart.style.left = Math.random() * 100 + "%";
        heart.style.animationDuration = Math.random() * 3 + 2 + "s";
        heart.textContent = "❤️";
        header.appendChild(heart);

        setTimeout(() => heart.remove(), 4000);
    }

    setInterval(createHeart, 500);
});
</script>
</body>
</html>
