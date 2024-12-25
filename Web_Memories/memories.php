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

    // Lấy danh sách nhật ký theo ngày
    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <link rel="stylesheet" href="/web_memories/css/memories.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript -->
    <script>
        function showwritememoriesForm() {
            window.location.href = 'writememories.php';
        }

        function showImages() {
            window.location.href = 'viewimages1.php';
        }

        function Reload(){
            window.location.href = 'memories.php';
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
    <script>
        function showForm(form) {
            document.querySelectorAll('.form').forEach(f => f.classList.remove('active'));
            document.querySelectorAll('.tab button').forEach(t => t.classList.remove('active'));
            document.getElementById(form).classList.add('active');
            document.getElementById(form + '-tab').classList.add('active');
        }
    </script>
    <script>
        function updateFileNameAndPreview(inputId, labelId, previewId) {
            const input = document.getElementById(inputId);
            const label = document.getElementById(labelId);
            const imagePreview = document.getElementById(previewId);

            if (input.files.length > 0) {
                // Hiển thị tên tệp đã chọn
                label.textContent = input.files[0].name;

                // Đọc và hiển thị ảnh
                const file = input.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Hiển thị ảnh trong thẻ div có id `previewId`
                    imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-50">';
                    imagePreview.style.display = 'block'; // Hiện phần xem trước ảnh
                }

                reader.readAsDataURL(file); // Đọc nội dung ảnh dưới dạng URL
            } else {
                // Nếu không có tệp nào được chọn, đặt lại nhãn và ẩn ảnh
                label.textContent = "Tải ảnh lên";
                imagePreview.style.display = 'none';
            }
        }
    </script>
    <?php
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        // Lấy dữ liệu khách hàng từ cơ sở dữ liệu
        $datayourlove = [];
        if ($id_tk) {
            $datacustomer = ThongTinKhachHang($conn, $id_tk);
        }
        $hotenban = explode(' ', !empty($datacustomer['HOTEN']) ? $datacustomer['HOTEN'] : null);
        $tenban = mb_strtoupper(end($hotenban));
        // Tìm người hẹn hò
        $datayourlove = [];
        if ($id_tk) {
            $datayourlove = TimNguoiHenHo($conn, $email);
        }
        $hotenny = explode(' ', !empty($datayourlove['HOTEN']) ? $datayourlove['HOTEN'] : null);
        $tenny = mb_strtoupper(end($hotenny));

        $memo = [];
         // Lấy ngày hiện tại
        $ngay = date("d");
        // Lấy tháng hiện tại
        $thang = date("m");
        //Lấy năm hiện tại
        $nam = date("Y");

        $thangFind = isset($_GET['month']) ? $_GET['month'] : $thang;
        $namFind = isset($_GET['year']) ? $_GET['year'] : $nam;

        //SINH NHẬT
        $ngaysinhtemp1 = isset($datacustomer['NGAYSINH']) ? $datacustomer['NGAYSINH'] : null;
        if($ngaysinhtemp1 != null){
            $daybir = new Datetime($ngaysinhtemp1);
            $ngaybir = $daybir->format("d");
            $thangbir = $daybir->format("m");
            $nambir = $daybir->format("Y");
    
            if($ngaybir == $ngay && $thangbir == $thang){
                $memo['Birthday'] = 'HappyBirthday';
            }
        }
        
        //SINH NHẬT NGƯỜI YÊU
        $ngaysinhtemp2 = isset($datayourlove['NGAYSINH']) ? $datayourlove['NGAYSINH'] : null;
        if($ngaysinhtemp2 != null){
            $daybirlove = new Datetime($ngaysinhtemp2);
            $ngaybirlove = $daybirlove->format("d");
            $thangbirlove = $daybirlove->format("m");
            $nambirlove = $daybirlove->format("Y");
    
            if($ngaybirlove == $ngay && $thangbirlove == $thang){
                $memo['BirthdayLove'] = 'HappyBirthdayLove';
            }
        }
        //KỈ NIỆM
        $ngayhenho = "";
        $sql = "SELECT NGAYHENHO FROM HENHO WHERE ID_HH = (SELECT ID_HH FROM KHACHHANG WHERE ID_TK = ?)";
        $stmt = $conn->prepare($sql);
        if($stmt){
            $stmt->bind_param("s", $id_tk);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result === false) {
                return;
            }
            if($result->num_rows == 1){
                $row = $result->fetch_assoc();
                $ngayhenho = $row["NGAYHENHO"];
                $timelove = ThoiGianYeu($ngayhenho);
                // Convert to desired format
                $date = new DateTime($ngayhenho);
                $ngayhenho = $date->format('d');
                $thanghenho = $date->format('m');
                $namhenho = $date->format('Y');
            }        
        }
        if($ngayhenho == $ngay) {
            $memo['Memories'] = 'MemoriesLove';
        }
        $stmt->close();
    ?>
    <?php
        $anh1 = $noidung = $noidungrep = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!empty($_POST['comment']) && !empty($_POST['id_kn'])) {
                $noidung = $_POST['comment'];
                $id_kn = intval($_POST['id_kn']);
                $id_kh = $datacustomer['ID_KH'];
        
                $sql = "INSERT INTO BINHLUAN (NOIDUNG, ID_KH, ID_KN) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("sii", $noidung, $id_kh, $id_kn);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
            if (!empty($_POST['reply_comment']) && !empty($_POST['id_bl'])){
                $noidungrep = $_POST['reply_comment'];
                $id_bl = intval($_POST['id_bl']);
                $id_kh = $datacustomer['ID_KH'];
        
                $sql = "INSERT INTO TRALOIBINHLUAN (NOIDUNG, ID_BL, ID_KH) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("sii", $noidungrep, $id_bl, $id_kh);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
            if(!empty($_POST['mess_love']) || !empty($_POST['mess_bir'])){
                if (!empty($_POST['mess_love'])){
                    $loai = 'MEMORIES';
                    $contentlove = htmlspecialchars($_POST['mess_love']);
                    $anh1 = uploadImages('imglove');
                }
                elseif(!empty($_POST['mess_bir'])){
                    $loai = 'BIRTHDAY';
                    $contentlove = htmlspecialchars($_POST['mess_bir']);
                    $anh1 = uploadImages('imgbir');
                }
                if (strpos($anh1, 'upload/') !== false){
                    $sql = "INSERT INTO LOICHUC(NOIDUNG, ANH, LOAILC, ID_KH) VALUES (?,?,?,?)";
                    $stmt = $conn->prepare($sql);
                    if($stmt){
                        $stmt->bind_param("sssi", $contentlove, $anh1, $loai, $datacustomer['ID_KH']);
                        $stmt->execute();
                    }
                    $stmt->close();
                    // Chuyển hướng sau khi xử lý biểu mẫu
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }
            if(isset($_POST['memories_id'])){
                $id_kn = $_POST['memories_id'];
                $sql = "UPDATE KINIEM SET TRANGTHAIXOA = 1 WHERE ID_KN = ?";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("i", $id_kn);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
            if(isset($_POST['id_bldelete'])){
                $id_bl = intval($_POST['id_bldelete']);
                $sql = 'UPDATE BINHLUAN SET TRANGTHAIXOA = 1 WHERE ID_BL = ?';
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("i", $id_bl);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        
            if(isset($_POST['id_repdelete'])){
                $id_rep = intval($_POST['id_repdelete']);
                $sql = 'UPDATE TRALOIBINHLUAN SET TRANGTHAIXOA = 1 WHERE ID_REP = ?';
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("i", $id_rep);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        
            if(isset($_POST['save_edit'])){
                $id_bl = intval($_POST['id_bl']);
                $noidung = $_POST['updated_comment'];
                $sql = "UPDATE BINHLUAN SET NOIDUNG = ? WHERE ID_BL = ?";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("si", $noidung, $id_bl);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        
            if(isset($_POST['save_reply_edit'])){
                $id_rep = intval($_POST['id_reply']);
                $noidung = $_POST['updated_reply'];
                $sql = "UPDATE TRALOIBINHLUAN SET NOIDUNG = ? WHERE ID_REP = ?";
                $stmt = $conn->prepare($sql);
                if($stmt){
                    $stmt->bind_param("si", $noidung, $id_rep);
                    $stmt->execute();
                }
                $stmt->close();
                // Chuyển hướng sau khi xử lý biểu mẫu
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
        
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
        <div class="ngang">
            <b><h5>Kỉ niệm hôm nay ngày:</h5></b>
            <span><b><h5><?php echo htmlspecialchars(date('d/m/Y')); ?></h5></b></span>
        </div>
        <div class="group">
        <?php
            if($memo != null){
                foreach($memo as $x){
                    if($x == "MemoriesLove"){
                        echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" enctype="multipart/form-data">';
                        echo '<div class="post"><div class="ngang"><h2><b>HAPPY ANNIVERSARY</b></div></h2>';
                        echo '<div class="ngang"><b><h3>'.$tenban.' ❤ '.$tenny.'</h3></b></div>';
                        echo '<div class="ngang"><p>Hai bạn đã bên nhau được <b>'.$timelove->y.'</b> năm, <b>'.$timelove->m.'</b> tháng, đã được <b>'.$timelove->days.'</b> ngày ❤</p></div>';
                        echo '<div class="ngang"><input type="button" id="btn-blue" value="Xem ảnh kỉ niệm" onclick="showImages()"></div>';
                        echo '<br>';
                        echo '<div class="ngang">';
                        echo '<textarea id="mess_love" name="mess_love" placeholder="Gửi lời yêu thương đến đối phương..."></textarea>';
                        echo '<button type="submit" name="submit_love" class="submit-btn">
                                    <i class="bi bi-chat-heart"></i>
                              </button>';
                        echo '</div>';
                        echo '<br>';
                        echo '<div class="custom-file-upload">';
                        echo '<label for="imglove" id="fileLabelLove">Tải ảnh lên</label>';
                        echo '<input type="file" id="imglove" name="imglove" accept="image/*" onchange="updateFileNameAndPreview(\'imglove\', \'fileLabelLove\', \'imagePreviewLove\')">';
                        echo '</div>';
                        echo '<div id="imagePreviewLove" class="image-preview"></div>'; 
                        // Hiển thị lời chúc
                        echo '<hr>';
                        $loichuc = DanhSachLoiChuc($conn, 'MEMORIES');
                        if ($loichuc != null) {
                            foreach ($loichuc as $lc) {
                                echo '<div class="loichuc">';
                                echo '<div class="avatar"><img src="' . htmlspecialchars($lc['AVARTAR']) . '" alt="Avatar"></div>';
                                echo '<div class="user-info">';
                                echo '<h4>' . htmlspecialchars($lc['HOTEN']) . '</h4>';
                                echo '<p>' . htmlspecialchars($lc['NOIDUNG']) . '</p>';
                                if (!empty($lc['ANH'])) {
                                    echo '<div class="post-image ngang"><img src="' . htmlspecialchars($lc['ANH']) . '" alt="Image" class="w-25"></div>';
                                }
                                echo '</div>';
                                echo '</div>';
                                echo '<hr>';
                            }
                        }
                        echo '</div>';
                        echo '</form>';
                    }
                    if($x == "HappyBirthday"){
                        echo '<div class="post"><div class="ngang"><h2><b>HAPPY BIRTHDAY</b></div></h2>';
                        echo '<div class="ngang"><p> Chúc bạn sinh nhật vui vẻ!</p></div>';
                        echo '<div class="ngang">Tuổi<b>'.($nam - $nambir).'</b>nhiều thành công hơn nhé!</div>';
                        echo '<div class="ngang"><img src="/web_memories/images/banhkem.gif" class="w-25"></div>';
                        echo '</div>';
                    }
                    if($x == "HappyBirthdayLove"){
                        echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" enctype="multipart/form-data">';
                        echo '<div class="post"><div class="ngang"><h2><b>HAPPY BIRTHDAY YOUR LOVE</b></div></h2>';
                        echo '<div class="ngang">Hôm nay là sinh nhật<b>'.($nam - $nambirlove).'</b> tuổi của người yêu bạn</div>';
                        echo '<div class="ngang">Gửi lời chúc và dành những điều tốt đẹp cho bạn ấy nhé ❤</div>';
                        echo '<div class="ngang"><img src="/web_memories/images/banhkem.gif" class="w-25"></div>';
                        echo '<div class="ngang">';
                        echo '<textarea id="mess_bir" name="mess_bir" placeholder="Gửi lời yêu thương đến đối phương..."></textarea>';
                        echo '<button type="submit" name="submit_bir" class="submit-btn">
                                    <i class="bi bi-chat-heart"></i>
                              </button>';
                        echo '</div>';
                        echo '<br>';
                        echo '<div class="custom-file-upload">';
                        echo '<label for="imgbir" id="fileLabelBir">Tải ảnh lên</label>';
                        echo '<input type="file" id="imgbir" name="imgbir" accept="image/*" onchange="updateFileNameAndPreview(\'imgbir\', \'fileLabelBir\', \'imagePreviewBir\')">';
                        echo '</div>';
                        echo '<div id="imagePreviewBir" class="image-preview"></div>';                         
                        // Hiển thị lời chúc
                        echo '<hr>';
                        $loichuc = DanhSachLoiChuc($conn, 'BIRTHDAY');
                        if ($loichuc != null) {
                            foreach ($loichuc as $lc) {
                                echo '<div class="loichuc">';
                                echo '<div class="avatar"><img src="' . htmlspecialchars($lc['AVARTAR']) . '" alt="Avatar"></div>';
                                echo '<div class="user-info">';
                                echo '<h4>' . htmlspecialchars($lc['HOTEN']) . '</h4>';
                                echo '<p>' . htmlspecialchars($lc['NOIDUNG']) . '</p>';
                                if (!empty($lc['ANH'])) {
                                    echo '<div class="post-image ngang"><img src="' . htmlspecialchars($lc['ANH']) . '" alt="Image" class="w-25"></div>';
                                }
                                echo '</div>';
                                echo '</div>';
                                echo '<hr>';
                            }
                        }
                        echo '</div>';
                        echo '</form>';
                        echo '<hr>';
                    }
                }
            }
        ?>
        <div id="lightbox" class="lightbox">
            <span class="close">&times;</span>
            <img id="lightbox-image" src="" alt="Expanded view">
        </div>
        <script>
        // Xử lý ảnh click để mở lightbox
        document.querySelectorAll('.post-image img').forEach(image => {
            image.addEventListener('click', function() {
                const lightbox = document.getElementById('lightbox');
                const lightboxImage = document.getElementById('lightbox-image');
                lightboxImage.src = this.src;
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
        </div>
        <div class="group">
            <span><input type="button" id="btn-pink" value="Thêm kỉ niệm mới" onclick="showwritememoriesForm()"></span>
        </div>
        <hr>
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="ngang">
                <div>
                    <label for="months">Chọn tháng:</label>
                    <select id="months" name="month">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php if ($i == $thangFind) echo 'selected'; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="years">Chọn năm:</label>
                    <select id="years" name="year">
                        <?php 
                        $nam = date("Y");
                        for ($i = $namhenho; $i <= $nam; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php if ($i == $namFind) echo 'selected'; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <input type="submit" id="btn-blue" value="Xem kỉ niệm">
                </div>
            </div>
        </form>
        <div class="user-container">
            <div class="user">
                <?php
                    // Lấy dữ liệu kỷ niệm
                    $id_kh = !empty($datacustomer['ID_KH']) ? $datacustomer['ID_KH'] : null;
                    $id_love = !empty($datayourlove['ID_KH']) ? $datayourlove['ID_KH'] : null;
                    $memoriesdata = ThongTinKiNiem($conn, $id_kh, $id_love, $thangFind, $namFind);
                    $_SESSION['thang'] = $thangFind;
                    $_SESSION['nam'] = $namFind;
                    // Hiển thị tiêu đề
                    echo '<b><h4 class="text-info">Kỉ niệm tháng ' . htmlspecialchars($thangFind) . '</h4></b>';
                    echo '<hr>';

                    // Kiểm tra dữ liệu kỷ niệm
                    if ($memoriesdata != null && count($memoriesdata) > 0) {
                        foreach ($memoriesdata as $memory) {
                            $_SESSION['id_kn'] = $memory['ID_KN'];
                            // Lấy danh sách ảnh kỷ niệm cho từng kỷ niệm
                            $id_kn = isset($memory['ID_KN']) ? $memory['ID_KN'] : null;
                            $memoriesimg = DanhSachAnhKiNiem($conn, $id_kn);
                            
                            // Xử lý nội dung
                            list($noidung1, $noidung2) = splitString($memory['MOTA'], 1200);

                            // Lấy ngày kỷ niệm
                            $datekiniem = new DateTime($memory['NGAYKINIEM']);
                            $thangkiniem = $datekiniem->format('m');
                            $namkiniem = $datekiniem->format('Y');

                            //Lấy danh sách bình luân
                            $datacomment = DanhSachBinhLuan($conn, $memory['ID_KN']);
                            // Kiểm tra tháng và năm
                            if ($thangkiniem == $thangFind && $namkiniem == $namFind) {
                ?>
                <div class="post">
                    <div class="content">
                        <div class="images">
                            <?php
                            if ($memoriesimg != null) {
                                $soluong = count($memoriesimg);
                                $count = 0;
                                foreach ($memoriesimg as $index => $img) {
                                    $count++;
                                    if ($count <= 5) {
                                        echo '<a href="viewimages.php?id_kn=' . htmlspecialchars($memory['ID_KN']) . '&start_index=' . $index . '" class="image" style="background-image: url(' . htmlspecialchars($img['TENANH']) . ');"></a>';
                                    } 
                                    else {
                                        echo '<a href="viewimages.php?id_kn=' . htmlspecialchars($memory['ID_KN']) . '&start_index=' . $index . '" class="image more" style="background-image: url(' . htmlspecialchars($img['TENANH']) . ');">';
                                        echo '<div class="overlay">+' . ($soluong - 5) . '</div>';
                                        echo '</a>';
                                        break;
                                    }
                                }
                            }
                            ?>
                        </div>
                        <div class="text">
                            <div class="ngang-left">
                                <i class="bi bi-heart-fill red-icon"></i>
                                <span><p>Người viết: <b><?php echo htmlspecialchars($memory['NGUOIVIET']); ?></b></p></span>
                                <?php
                                     if($memory['NGUOIVIET'] == $datacustomer['HOTEN']){
                                        echo '<form method="GET" action="writememories.php">';
                                        echo '<input type="hidden" name="memories_id" value="' . htmlspecialchars($memory['ID_KN']) . '">';
                                        echo '<button type="submit" name="edit" class="edit-btn">
                                                    <i class="bi bi-pencil-square"></i>
                                            </button>';
                                        echo '</form>';
                                        echo '<form method="POST" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'">';
                                        echo '<input type="hidden" name="memories_id" value="' . htmlspecialchars($memory['ID_KN']) . '">';
                                        echo '<button type="button" class="delete-btn" data-id="' . htmlspecialchars($memory['ID_KN']) . '" data-toggle="modal" data-target="#confirmDeleteModal">
                                              <i class="bi bi-trash"></i>
                                              </button>';
                                        echo '</form>';
                                     }
                                ?>
                            </div>
                            <hr>
                            <h5><?php echo htmlspecialchars($memory['TENKINIEM']); ?></h5>
                            <h6 class="text-black-50">Ngày: <?php echo htmlspecialchars($memory['NGAYKINIEM']); ?></h6>
                            <hr>
                            <p><?php echo htmlspecialchars($noidung1); ?></p>
                        </div>
                    </div>
                    <hr>
                    <p><?php echo htmlspecialchars($noidung2); ?></p>
                    <div class="comment">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <div class="ngang">
                                <textarea id="comment" name="comment" placeholder="Viết bình luận..."></textarea>
                                <input type="hidden" name="id_kn" value="<?php echo htmlspecialchars($memory['ID_KN']); ?>">
                                <button type="submit" name="submit" class="submit-btn">
                                    <i class="bi bi-chat-heart"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <hr>
                    <?php
                        // Hiển thị bình luận
                        if ($datacomment != null) {
                            foreach ($datacomment as $cmt) {
                                echo '<div class="comment-container">';
                                echo '<img src="' . htmlspecialchars($cmt['AVARTAR']) . '" alt="User profile picture">';
                                echo '<div>';
                                echo '<div class="comment-content">';
                                echo '<div class="comment-header">' . htmlspecialchars($cmt['HOTEN']) . '</div>';
                                
                                // Kiểm tra nếu là chế độ chỉnh sửa
                                if (isset($_POST['edit_id']) && $_POST['edit_id'] == $cmt['ID_BL']) {
                                    echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
                                    echo '<input type="hidden" name="id_bl" value="' . htmlspecialchars($cmt['ID_BL']) . '">';
                                    echo '<textarea id="updated_comment_' . $cmt['ID_BL'] . '" name="updated_comment" class="edit-textarea">' . htmlspecialchars($cmt['NOIDUNG']) . '</textarea>';
                                    echo '</div>';
                                    echo '<div class="comment-footer">';
                                    echo '<span>' . htmlspecialchars($cmt['NGAYBINHLUAN']) . '</span>';
                                    echo '<span><button type="submit" id="save_edit" name="save_edit" class="save-btn">Lưu</button></span>';
                                    echo '<span><button type="button" class="cancel-btn" onclick="Reload()">Hủy</button></span>';
                                    echo '</div>';
                                    echo '</form>';
                                } else {
                                    echo '<div class="comment-text">' . htmlspecialchars($cmt['NOIDUNG']) . '</div>';
                                    echo '</div>';
                                    echo '<div class="comment-footer">';
                                    echo '<span>' . htmlspecialchars($cmt['NGAYBINHLUAN']) . '</span>';
                                    echo '<span class="reply-button">Phản hồi</span>';
                                    if ($cmt['HOTEN'] == $datacustomer['HOTEN']) {
                                        echo '<span><button class="edit-button" onclick="editComment(' . $cmt['ID_BL'] . ')">Sửa</button></span>';
                                    }
                                    echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" style="display:inline;">';
                                    echo '<input type="hidden" id="id_bldelete" name="id_bldelete" value="' . htmlspecialchars($cmt['ID_BL']) . '">';
                                    echo '<span><button type="submit" class="delete-button">Xóa</button></span>';
                                    echo '</form>';
                                    echo '</div>';
                                }

                                // Form phản hồi bình luận
                                echo '<div class="response-input">';
                                echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
                                echo '<input type="hidden" name="id_bl" value="' . htmlspecialchars($cmt['ID_BL']) . '">';
                                echo '<input type="text" id="reply_comment" name="reply_comment" placeholder="Viết phản hồi...">';
                                echo '<button type="submit" name="submit_reply" class="submit-btn">';
                                echo '<i class="bi bi-chat-heart"></i>';
                                echo '</button>';
                                echo '</form>';
                                echo '</div>';

                                // Hiển thị các phản hồi của bình luận này
                                $replies = DanhSachTraLoiBinhLuan($conn, $cmt['ID_BL']);
                                if ($replies != null) {
                                    foreach ($replies as $reply) {
                                        echo '<div class="reply-container">';
                                        echo '<div class="comment-container">';
                                        echo '<img src="' . htmlspecialchars($reply['AVARTAR']) . '" alt="User profile picture">';
                                        echo '<div>';
                                        echo '<div class="comment-content">';
                                        echo '<div class="comment-header">' . htmlspecialchars($reply['HOTEN']) . '</div>';

                                        // Kiểm tra nếu là chế độ chỉnh sửa phản hồi
                                        if (isset($_POST['edit_reply_id']) && $_POST['edit_reply_id'] == $reply['ID_REP']) {
                                            echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
                                            echo '<input type="hidden" name="id_reply" value="' . htmlspecialchars($reply['ID_REP']) . '">';
                                            echo '<textarea id="updated_reply_' . $reply['ID_REP'] . '" name="updated_reply" class="edit-textarea">' . htmlspecialchars($reply['NOIDUNG']) . '</textarea>';
                                            echo '<div class="comment-footer">';
                                            echo '<span>' . htmlspecialchars($reply['NGAYTRALOI']) . '</span>';
                                            echo '<span><button type="submit" id="save_reply_edit" name="save_reply_edit" class="save-btn">Lưu</button></span>';
                                            echo '<span><button type="button" class="cancel-btn" onclick="Reload()">Hủy</button></span>';
                                            echo '</div>';
                                            echo '</form>';
                                        } else {
                                            echo '<div class="comment-text">' . htmlspecialchars($reply['NOIDUNG']) . '</div>';
                                            echo '</div>';
                                            echo '<div class="comment-footer">';
                                            echo '<span>' . htmlspecialchars($reply['NGAYTRALOI']) . '</span>';
                                            if ($reply['HOTEN'] == $datacustomer['HOTEN']) {
                                                echo '<span><button class="edit-button" onclick="editReply(' . $reply['ID_REP'] . ')">Sửa</button></span>';
                                            }
                                            echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" style="display:inline;">';
                                            echo '<input type="hidden" id="id_repdelete" name="id_repdelete" value="' . htmlspecialchars($reply['ID_REP']) . '">';
                                            echo '<span><button type="submit" class="delete-button">Xóa</button></span>';
                                            echo '</form>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }

                                echo '</div>';
                                echo '</div>';
                            }
                        }

                        ?>
                </div>
                <?php
                            } // Kết thúc if($thangkiniem == $thangFind && $namkiniem == $namFind)
                        }
                    }
                    else {
                        echo "<div class='ngang'><p>Bạn và người ấy chưa cập nhật kỉ niệm nào!</p></div>";
                    }
                    } else {
                        echo '<div class="post ngang">Bạn hãy tìm đối tượng hẹn hò để sử dụng tính năng này nhé!</div>';
                    }
                ?>
            </div>
            <?php
            
            ?>
        </div>
        <hr>
        <?php
        } //đóng if 1
        else{
            echo '<div class="post ngang">Bạn hãy tìm đối tượng hẹn hò để sử dụng tính năng này nhé!</div>';
        }
        ?>
        <?php } ?>
    </div>
    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Xóa kỉ niệm?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa kỉ niệm này?
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
                        'name': 'memories_id',
                        'value': diaryIdToDelete
                    })).appendTo('body').submit();
                }
            });
        });
    </script>
    </main>
    <script>
            document.querySelectorAll('.reply-button').forEach(button => {
                button.addEventListener('click', function() {
                    const replyInput = this.parentElement.nextElementSibling;
                    if (replyInput.style.display === 'flex') {
                        replyInput.style.display = 'none';
                    } else {
                        replyInput.style.display = 'flex';
                    }
                });
            });
    </script>
    <script>
        function editComment(commentId) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'edit_id';
            input.value = commentId;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        function editReply(replyId) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'edit_reply_id';
            input.value = replyId;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        window.onload = function () {
            const editId = "<?php echo isset($_POST['edit_id']) ? $_POST['edit_id'] : ''; ?>";
            if (editId) {
                setTimeout(function () {
                    const commentTextarea = document.getElementById('updated_comment_' + editId);
                    if (commentTextarea) {
                        commentTextarea.focus();
                    }
                }, 100);
            }

            const editReplyId = "<?php echo isset($_POST['edit_reply_id']) ? $_POST['edit_reply_id'] : ''; ?>";
            if (editReplyId) {
                setTimeout(function () {
                    const replyTextarea = document.getElementById('updated_reply_' + editReplyId);
                    if (replyTextarea) {
                        replyTextarea.focus();
                    }
                }, 100);
            }
        };
    </script>
</body>
</html>
