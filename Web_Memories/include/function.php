<?php
function checkExists($conn, $column, $value) {
    $sql = "SELECT * FROM taikhoan WHERE $column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $value);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result == false) {
        return;
    }
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return true;
    }
    
    $stmt->close();
    return false;
}

function CheckTrangThaiHenHo($conn, $value){
    $sql = "SELECT TRANGTHAI FROM HENHO WHERE ID_HH = (SELECT ID_HH FROM KHACHHANG WHERE ID_KH = ?)";
    $stmt = $conn->prepare($sql);
    if($stmt){
        $stmt->bind_param('i', $value);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows == 1) {
            $trangthai = $result->fetch_assoc();
            return $trangthai['TRANGTHAI'];
        } else {
            return null;
        }
    }
    return null;
}

function CheckMaKhachHang($conn, $value) {
    $sql = "SELECT SP_KIEMTRA_MAKH(?) AS result";
    $stmt = $conn->prepare($sql);
    
    if($stmt) {
        $stmt->bind_param('s', $value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result == false) {
            return;
        }
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['result'];
        }
    }
    
    $stmt->close();
    return false;
}


function TimNguoiHenHo($conn, $value){
    $procedure = "CALL SP_TIMNGUOIHENHO(?)";
    $stmt = $conn->prepare($procedure);
    $stmt->bind_param('s', $value);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result == false) {
        return;
    }
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $customerInfo = [
            'ID_KH' => $row['ID_KH'],
            'HOTEN' => $row['HOTEN'],
            'SDT' => $row['SDT'],
            'NGAYSINH' => $row['NGAYSINH'],
            'GIOITINH' => $row['GIOITINH'],
            'DIACHI' => $row['DIACHI'],
            'AVARTAR' => $row['AVARTAR'],
            'MAKH' => $row['MAKH'],
            'ID_HH' => $row['ID_HH']
        ];
    } else {
        $customerInfo = null;
    }
    
    $stmt->close(); 
    return $customerInfo;
}

function ThongTinKhachHang($conn, $value) {
    $sql1 = "SELECT * FROM KHACHHANG WHERE ID_TK = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param('i', $value);
    $stmt1->execute();
    $result = $stmt1->get_result();
    if ($result == false) {
        return;
    }
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $customerInfo = [
            'ID_KH' => $row['ID_KH'],
            'HOTEN' => $row['HOTEN'],
            'SDT' => $row['SDT'],
            'NGAYSINH' => $row['NGAYSINH'],
            'GIOITINH' => $row['GIOITINH'],
            'DIACHI' => $row['DIACHI'],
            'AVARTAR' => $row['AVARTAR'],
            'MAKH' => $row['MAKH'],
            'DIEMTINHYEU' => $row['DIEMTINHYEU'],
            'ID_HH' => $row['ID_HH']
        ];
    } else {
        $customerInfo = null;
    }

    $stmt1->close();

    return $customerInfo;
}

function ThongTinUser($conn, $value) {
    $sql1 = "SELECT * FROM KHACHHANG WHERE MAKH = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param('i', $value);
    $stmt1->execute();
    $result = $stmt1->get_result();
    if ($result == false) {
        echo "Không tìm thấy người dùng này!";
    }
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $customerInfo = [
            'ID_KH' => $row['ID_KH'],
            'HOTEN' => $row['HOTEN'],
            'SDT' => $row['SDT'],
            'NGAYSINH' => $row['NGAYSINH'],
            'GIOITINH' => $row['GIOITINH'],
            'DIACHI' => $row['DIACHI'],
            'AVARTAR' => $row['AVARTAR'],
            'MAKH' => $row['MAKH']
        ];
    } else {
        $customerInfo = null;
    }

    $stmt1->close();

    return $customerInfo;
}

function DanhSachNhatKyTheoNgay($conn, $id_kh, $startDate, $endDate) {
    $query = "SELECT * FROM NHATKY WHERE ID_KH = ? AND TRANGTHAIXOA = 0";
    $params = [$id_kh];
    $types = "i";

    if (!empty($startDate) && !empty($endDate)) {
        $query .= " AND NGAYVIET BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ss";
    } elseif (!empty($startDate)) {
        $query .= " AND NGAYVIET >= ?";
        $params[] = $startDate;
        $types .= "s";
    } elseif (!empty($endDate)) {
        $query .= " AND NGAYVIET <= ?";
        $params[] = $endDate;
        $types .= "s";
    }

    $query .= " ORDER BY NGAYVIET DESC";
    $stmt = $conn->prepare($query);

    $stmt->bind_param($types, ...$params);

    $stmt->execute();
    $result = $stmt->get_result();
    $diaries = [];
    while ($row = $result->fetch_assoc()) {
        $diaries[] = $row;
    }
    return $diaries;
}

function LayNhatKyTheoID($conn, $value){
    $sql = "SELECT * FROM NHATKY WHERE ID_NK = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $diaries = [];
    if ($result == false) {
        echo "Không tìm thấy người dùng này!";
    }
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $diaries = [
            'ID_NK' => $row['ID_NK'],
            'TIEUDE' => $row['TIEUDE'],
            'NOIDUNG' => $row['NOIDUNG'],
            'NGAYVIET' => $row['NGAYVIET'],
            'ANH_NK' => $row['ANH_NK'],
            'TRANGTHAIXOA' => $row['TRANGTHAIXOA'],
            'ID_KH' => $row['ID_KH']
        ];
    } else {
        $diaries = null;
    }

    $stmt->close();

    return $diaries;

}

function LayIDKiNiem($conn){
    $sql = "SELECT * FROM KINIEM ORDER BY ID_KN DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result == false) {
        echo "Không tìm thấy kỉ niệm người dùng này!";
    }
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $memories = [
            'ID_KN' => $row['ID_KN'],
            'TENKINIEM' => $row['TENKINIEM'],
            'NGAYKINIEM' => $row['NGAYKINIEM'],
            'MOTA' => $row['MOTA'],
            'NGUOIVIET' => $row['NGUOIVIET']
        ];
    } else {
        $memories = null;
    }

    $stmt->close();

    return $memories;
}
function LayKiNiemTheoID($conn, $value){
    $sql = "SELECT * FROM KINIEM WHERE ID_KN = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $memories = [];
    if ($result == false) {
        echo "Không tìm thấy người dùng này!";
    }
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $memories = [
            'ID_KN' => $row['ID_KN'],
            'TENKINIEM' => $row['TENKINIEM'],
            'NGAYKINIEM' => $row['NGAYKINIEM'],
            'MOTA' => $row['MOTA'],
            'NGUOIVIET' => $row['NGUOIVIET']
        ];
    } else {
        $memories = null;
    }

    $stmt->close();

    return $memories;

}
function ThongTinKiNiem($conn, $value1, $value2, $month, $year) {
    $sql = "SELECT * FROM KINIEM 
            WHERE ID_KN IN (
                SELECT ID_KN 
                FROM CHITIETKINIEM 
                WHERE ID_KH = ? OR ID_KH = ?
            )
            AND TRANGTHAIXOA = 0
            AND MONTH(NGAYKINIEM) = ? 
            AND YEAR(NGAYKINIEM) = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiii', $value1, $value2, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $memories = [];
    
    if ($result == false) {
        echo "Không tìm thấy kỉ niệm cho người dùng này!";
    } elseif ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            $memories[] = [
                'ID_KN' => $row['ID_KN'],
                'TENKINIEM' => $row['TENKINIEM'],
                'NGAYKINIEM' => $row['NGAYKINIEM'],
                'MOTA' => $row['MOTA'],
                'NGUOIVIET' => $row['NGUOIVIET']
            ];
        }
    } else {
        $memories = null;
    }

    $stmt->close();

    return $memories;
}

function DanhSachAnhKiNiem($conn, $value) {
    $sql = "CALL SP_DANHSACH_ANHKINIEM(?)";
    $stmt = $conn->prepare($sql);
    $images = [];

    if ($stmt) {
        $stmt->bind_param("i", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $images[] = [
                    "ID_ANH" => $row["ID_ANH"],
                    "TENANH" => $row["TENANH"]
                ];
            }
        } else {
            $images = null;
        }
        $stmt->close();
    }

    return $images;
}

function ThemAnhKiNiem($conn, $uploadedFiles, $id_kn) {
    foreach ($uploadedFiles as $anh) {
        if (is_string($anh)) {
            $sql1 = "INSERT INTO ANHKINIEM (TENANH) VALUES (?)";
            $stmt1 = $conn->prepare($sql1);
            if ($stmt1) {
                $stmt1->bind_param("s", $anh);
                $stmt1->execute();
                $stmt1->close();
                
                $id_anh = $conn->insert_id;
                $sql2 = "INSERT INTO CHITIETANHKINIEM (ID_KN, ID_ANH) VALUES (?, ?)";
                $stmt2 = $conn->prepare($sql2);
                if ($stmt2) {
                    $stmt2->bind_param("ii", $id_kn, $id_anh);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }
        }
    }
}


function generateUniqueCodes() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $length = 8;
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function ThoiGianYeu($ngayyeu){
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $start_date = new DateTime($ngayyeu);
    $end_date = new DateTime('now');
    $interval = $start_date->diff($end_date);
    return $interval;
    
}

function splitString($input, $length = 1200) {
    $inputLength = strlen($input);
    if ($inputLength <= $length) {
        return [$input, ''];
    }
    $tempString = substr($input, 0, $length);
    $lastPeriodPos = strrpos($tempString, '.');
    if ($lastPeriodPos !== false) {
        $string1 = substr($input, 0, $lastPeriodPos + 1);
        $string2 = substr($input, $lastPeriodPos + 1);
    } else {
        $string1 = $tempString;
        $string2 = substr($input, $length);
    }

    return [$string1, $string2];
}

function DanhSachBinhLuan($conn, $value){
    $sql = "SELECT BL.ID_BL, BL.NOIDUNG, BL.NGAYBINHLUAN, BL.ID_KH, BL.ID_KN, KH.AVARTAR, KH.HOTEN 
            FROM binhluan BL INNER JOIN khachhang KH 
            ON BL.ID_KH = KH.ID_KH 
            WHERE ID_KN = ? AND TRANGTHAIXOA = 0";
    $stmt = $conn->prepare($sql);
    $comment = [];
    if ($stmt) {
        $stmt->bind_param("i", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $comment[] = [
                    "ID_BL" => $row["ID_BL"],
                    "NOIDUNG" => $row["NOIDUNG"],
                    "NGAYBINHLUAN" => $row['NGAYBINHLUAN'],
                    "ID_KH" => $row["ID_KH"],
                    "ID_KN" => $row["ID_KN"],
                    "AVARTAR" => $row["AVARTAR"],
                    "HOTEN" => $row["HOTEN"]
                ];
            }
        } else {
            $comment = null;
        }
        $stmt->close();
    }

    return $comment;
}

function DanhSachTraLoiBinhLuan($conn, $value){
    $sql = "SELECT BL.ID_REP, BL.NOIDUNG, BL.NGAYTRALOI, BL.ID_BL, BL.ID_KH, KH.AVARTAR, KH.HOTEN 
            FROM traloibinhluan BL INNER JOIN khachhang KH 
            ON BL.ID_KH = KH.ID_KH 
            WHERE BL.ID_BL = ? AND TRANGTHAIXOA = 0";
    $stmt = $conn->prepare($sql);
    $repcomment = [];
    if ($stmt) {
        $stmt->bind_param("i", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $repcomment[] = [
                    "ID_REP" => $row["ID_REP"],
                    "NOIDUNG" => $row["NOIDUNG"],
                    "NGAYTRALOI" => $row["NGAYTRALOI"],
                    "ID_BL" => $row["ID_BL"],
                    "ID_KH" => $row["ID_KH"],
                    "AVARTAR" => $row["AVARTAR"],
                    "HOTEN" => $row["HOTEN"]
                ];
            }
        } else {
            $repcomment = null;
        }
        $stmt->close();
    }

    return $repcomment;
}

function DanhSachLoiChuc($conn, $value){
    $sql = "SELECT LC.ID_LC, LC.NOIDUNG, LC.ANH, LC.NGAYCHUC, LC.LOAILC, LC.ID_KH, KH.HOTEN, KH.AVARTAR 
            FROM LOICHUC LC INNER JOIN KHACHHANG KH ON LC.ID_KH = KH.ID_KH
            WHERE LOAILC = ? AND NGAYCHUC = CURRENT_DATE()";
    $stmt = $conn->prepare($sql);
    $loichuc = [];
    if ($stmt) {
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $loichuc[] = [
                    "ID_LC" => $row["ID_LC"],
                    "NOIDUNG" => $row["NOIDUNG"],
                    "ANH" => $row["ANH"],
                    "NGAYCHUC" => $row["NGAYCHUC"],
                    "LOAILC" => $row["LOAILC"],
                    "ID_KH" => $row["ID_KH"],
                    "AVARTAR" => $row["AVARTAR"],
                    "HOTEN" => $row["HOTEN"]
                ];
            }
        } else {
            $loichuc = null;
        }
        $stmt->close();
    }

    return $loichuc;
}

function DanhSachCauHoi($conn, $value1, $value2){
    $cauhoi = [];
    $sql = "SELECT * FROM CAUHOI WHERE ID_GAME = ? AND ID_KH = ?";
    $stmt = $conn->prepare($sql);
    if($stmt){
        $stmt->bind_param("ii", $value1, $value2);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cauhoi[] = [
                    "ID_CH" => $row["ID_CH"],
                    "NOIDUNG" => $row["NOIDUNG"]
                ];
            }
        } 
        else{
            $cauhoi[] = null;
        } 
        $stmt->close();  
    }
    return $cauhoi;
}

function DanhSachCauTraLoi($conn, $value){
    $cautraloi = [];
    $sql = "SELECT * FROM DANHSACHCAUTRALOI WHERE ID_CH = ?";
    $stmt = $conn->prepare($sql);
    if($stmt){
        $stmt->bind_param("i", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cautraloi[] = [
                    "ID_CTL" => $row["ID_CTL"],
                    "NOIDUNG" => $row["NOIDUNG"],
                    "DAPAN" => $row["DAPAN"],
                    "ID_CH" => $row["ID_CH"]
                ];
            }
        } 
        else{
            $cautraloi[] = null;
        } 
        $stmt->close();  
    }
    return $cautraloi;
}

function LayDapAnDung($conn, $value){
    $sql = "SELECT NOIDUNG FROM DANHSACHCAUTRALOI WHERE ID_CH = ? AND DAPAN = 1";
    $stmt = $conn->prepare($sql);
    
    if($stmt){
        $stmt->bind_param("i", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows == 1) {
            $dapan = $result->fetch_assoc();
            return $dapan['NOIDUNG'];
        } else {
            return null;
        }
    }    
    return null;
}

function DanhSachThanhTich($conn, $value1, $value2, $value3){
    $sql = "SELECT KH.HOTEN, TT.ID_TT, TT.DIEM, TT.SOCAUDUNG, TT.NGAY FROM THANHTICH TT 
            INNER JOIN KHACHHANG KH ON TT.ID_KH = KH.ID_KH 
            WHERE ID_GAME = ? AND (TT.ID_KH = ? OR TT.ID_KH = ?)
            ORDER BY TT.ID_TT DESC";
    $stmt = $conn->prepare($sql);
    $thanhtich = [];
    if($stmt){
        $stmt->bind_param("iii", $value1, $value2, $value3);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            return;
        }
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $thanhtich[] = [
                    'HOTEN' => $row['HOTEN'],
                    'ID_TT' => $row['ID_TT'],
                    'DIEM' => $row['DIEM'],
                    'SOCAUDUNG' => $row['SOCAUDUNG'],
                    'NGAY' => $row['NGAY']
                ];
            }
        } 
        else{
            $thanhtich[] = null;
        } 
        $stmt->close();  
    }
    return $thanhtich;
}

function NoiDungTimKiem($conn, $value1, $value2){
    $resultSearch = [];
    $sql = "CALL SP_TIMKIEM(?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $value1, $value2);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result == false) {
        return [];
    }

    while ($row = $result->fetch_assoc()) {
        $resultSearch[] = [
            'ID' => $row['ID'],
            'TIEUDE' => $row['TIEUDE'],
            'NOIDUNG' => $row['NOIDUNG'],
            'NGAY' => $row['NGAY']
        ];
    }

    $stmt->close();
    return $resultSearch;
}

function XuLyDuLieuTimKiem($conn, $dataSearch){
    $dataNhatKy = [];
    $dataKiNiem = [];
    $dataGame = [];

    if (!empty($dataSearch)) {
        foreach ($dataSearch as $search) {
            // Tìm trong bảng NHATKY
            $sql1 = "SELECT * FROM NHATKY WHERE ID_NK = ? AND TIEUDE = ?";
            $stmt1 = $conn->prepare($sql1);
            if($stmt1){
                $stmt1->bind_param('is', $search['ID'], $search['TIEUDE']);
                $stmt1->execute();
                $result1 = $stmt1->get_result();
                while ($row = $result1->fetch_assoc()) {
                    $dataNhatKy[] = $row;
                }
            }
            // Tìm trong bảng KINIEM
            $sql2 = "SELECT * FROM KINIEM WHERE ID_KN = ? AND TENKINIEM = ?";
            $stmt2 = $conn->prepare($sql2);
            if($stmt2){
                $stmt2->bind_param('is', $search['ID'], $search['TIEUDE']);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                while ($row = $result2->fetch_assoc()) {
                    $dataKiNiem[] = $row;
                }
            }
            // // Tìm trong bảng GAME
            // $sql3 = "SELECT * FROM GAME WHERE ID_GAME = ? AND TENGAME = ?";
            // $stmt3 = $conn->prepare($sql3);
            // if($stmt3){
            //     $stmt3->bind_param('is', $search['ID'], $search['TIEUDE']);
            //     $stmt3->execute();
            //     $result3 = $stmt3->get_result();
            //     while ($row = $result3->fetch_assoc()) {
            //         $dataGame[] = $row;
            //     }
            // }
        }
    }

    return [
        'nhatKy' => $dataNhatKy,
        'kiNiem' => $dataKiNiem,
    ];
}

