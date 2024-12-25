<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['comment']) && !empty($_POST['id_kn'])) {
        $noidung = htmlspecialchars($_POST['comment']);
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
        $noidungrep = htmlspecialchars($_POST['reply_comment']);
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