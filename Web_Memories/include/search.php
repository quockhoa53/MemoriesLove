<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <link rel="stylesheet" href="/web_memories/css/search.css">
    <!-- Bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php
if (!empty($dataSearch)) {
    $result = XuLyDuLieuTimKiem($conn, $dataSearch);
    $nhatKy = !empty($result['nhatKy']) ? $result['nhatKy'] : null;
    $kiNiem = !empty($result['kiNiem']) ? $result['kiNiem'] : null;
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $datayourlove = [];
    if ($id_tk) {
        $datacustomer = ThongTinKhachHang($conn, $id_tk);
    }
    $hotenban = explode(' ', !empty($datacustomer['HOTEN']) ? $datacustomer['HOTEN'] : null);
    $tenban = mb_strtoupper(end($hotenban));
    if ($id_tk) {
        $datayourlove = TimNguoiHenHo($conn, $email);
    }
    $hotenny = explode(' ', !empty($datayourlove['HOTEN']) ? $datayourlove['HOTEN'] : null);
    $tenny = mb_strtoupper(end($hotenny));
?>
    <!-- Hiển thị kỉ niệm -->
    <div class="user-container">
        <div class="user">
            <h4 class="text"><b>Tất cả kỉ niệm</b></h4>
            <?php
                // Kiểm tra dữ liệu kỷ niệm
                if ($kiNiem != null && count($kiNiem) > 0) {
                    foreach ($kiNiem as $memory) {
                        $_SESSION['id_kn'] = $memory['ID_KN'];
                        // Lấy danh sách ảnh kỷ niệm cho từng kỷ niệm
                        $id_kn = isset($memory['ID_KN']) ? $memory['ID_KN'] : null;
                        $memoriesimg = DanhSachAnhKiNiem($conn, $id_kn);
                        
                        // Xử lý nội dung
                        list($noidung1, $noidung2) = splitString($memory['MOTA'], 1200);

                        // Lấy danh sách bình luận
                        $datacomment = DanhSachBinhLuan($conn, $memory['ID_KN']);
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
                                } else {
                                    echo '<a href="viewimages.php?id_kn=' . htmlspecialchars($memory['ID_KN']) . '&start_index=' . $index . '" class="image more" style="background-image: url(' . htmlspecialchars($img['TENANH']) . ');">';
                                    echo '<div class="overlay">+' . ($soluong - $count + 1) . '</div>';
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
                            echo '<div class="comment-text">' . htmlspecialchars($cmt['NOIDUNG']) . '</div>';
                            echo '</div>';
                            echo '<div class="comment-footer">';
                            echo '<span>' . htmlspecialchars($cmt['NGAYBINHLUAN']) . '</span>';
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
                                    echo '<div class="comment-text">' . htmlspecialchars($reply['NOIDUNG']) . '</div>';
                                    echo '</div>';
                                    echo '<div class="comment-footer">';
                                    echo '<span>' . htmlspecialchars($reply['NGAYTRALOI']) . '</span>';
                                    echo '</div>';
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
                    } // Kết thúc vòng lặp foreach $kiNiem
                } // Kết thúc kiểm tra $kiNiem
            ?>
        </div>
    </div>

    <!-- Hiển thị nhật kí -->
    <div class="user-container">
        <div class="user">
            <h4 class="text"><b>Tất cả bài nhật ký</b></h4>
            <?php
            if (isset($nhatKy) && $nhatKy != null && count($nhatKy) > 0) {
                foreach ($nhatKy as $diary) {
                    echo "<div class='postnhatky'>";
                    echo "<div class='post-header'>";
                    echo "<h2 class='post-title'>" . htmlspecialchars($diary['TIEUDE']) . "</h2>";
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
                echo "<p>Không tìm thấy bài nhật kí nào</p>";
            }
            ?>
        </div>
    </div>
<?php
} // Kết thúc kiểm tra $dataSearch
?>
</body>
</html>
