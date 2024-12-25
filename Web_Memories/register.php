<?php
    include("include/database.php");
    include("include/function.php");
    include("include/mail.php");
    $emailSent = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love - Register</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/login.css">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <script>
        function showLoginForm() {
            window.location.href = 'login.php';
        }
    </script>
</head>
<body>
    <?php
        $mess = "";
        $rancode = null;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password'])) {
                $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
                $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
                $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);      
                if (checkExists($conn, 'email', $email)) {
                    $mess = "Email đã được sử dụng!";
                } elseif (checkExists($conn, 'username', $username)) {
                    $mess = "Username đã được sử dụng!";
                } else {
                    try {
                        $mail->addAddress($email, $username);
                        $rancode = mt_rand(100000, 999999);
                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = 'Mã xác nhận đăng ký tài khoản';
                        $mail->Body    = 'Mã xác nhận của bạn: ' . $rancode;
                        $mail->AltBody = 'Mã xác nhận của bạn: ' . $rancode;

                        if ($mail->send()) {
                            $emailSent = true;
                            $mess = 'Mã xác nhận đã được gửi, vui lòng kiểm tra email!';
                            session_start();
                            $_SESSION['rancode'] = $rancode;
                            $_SESSION['email'] = $email;
                            $_SESSION['username'] = $username;
                            $_SESSION['password'] = $password;
                        }
                    } catch (Exception $exception) {
                        $mess = "Có lỗi xảy ra! Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            } 
        }

        // Kiểm tra mã xác nhận
        if (isset($_POST['code'])) {
            session_start();
            $code = filter_input(INPUT_POST, "code", FILTER_SANITIZE_SPECIAL_CHARS);
            if ($code == $_SESSION['rancode']) {
                $hash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);
                $sql = "INSERT INTO TAIKHOAN (EMAIL, USERNAME, PASSWORD) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sss', $_SESSION['email'], $_SESSION['username'], $hash);
                try {
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        echo "<script>
                            alert('Đăng ký thành công!');
                            window.location.href = 'login.php';
                        </script>";
                        exit();
                    } else {
                        $mess = "Đăng ký không thành công!";
                    }
                } catch (mysqli_sql_exception $e) {
                    $mess = "Đăng ký không thành công!";
                }
                $stmt->close();
            } else {
                $mess = "Mã xác nhận không đúng!";
            }
        }
    ?>
    <div class="login-container">
        <div id="register-form" class="form-container" style="<?php echo $emailSent ? 'display:none;' : 'display:block;'; ?>">
            <form class="register-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="new-username">Tên đăng nhập:</label>
                    <input type="text" id="new-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="new-password">Mật khẩu:</label>
                    <input type="password" id="new-password" name="password" required>
                </div>
                <div class="form-group">
                    <input type="submit" id="btn-register" name="register" value="Xác nhận">
                    <input type="button" value="Thoát" class="register-button" onclick="showLoginForm()">
                </div>
                <br>
                <span class="error"><?php echo $mess;?></span>
            </form>
        </div>
        <div id="codeForm" class="form-container" style="<?php echo $emailSent ? 'display:block;' : 'display:none;'; ?>">
            <form class="register-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="code">Mã xác nhận:</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="register" value="Đăng ký">
                    <input type="button" value="Thoát" class="register-button" onclick="showLoginForm()">
                </div>
                <br>
                <span class="error"><?php echo $mess;?></span>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    mysqli_close($conn);
?>
