<?php
    include("include/database.php");
    include("include/function.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love - Login</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/login.css">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <script>
        function showRegisterForm() {
            window.location.href = 'register.php';
        }
        function LoginFacebook() {
            window.location.href = 'fb-login.php';
        }
    </script>
</head>
<body class="body-login">
    <?php
        $mess = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (empty($username) || empty($password)) {
                $mess = "Vui lòng nhập đầy đủ thông tin đăng nhập!";
            } else {
                $sql = "SELECT ID_TK, EMAIL, USERNAME, PASSWORD FROM TAIKHOAN WHERE USERNAME = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $user_id = $row['ID_TK'];
                    $email = $row['EMAIL'];
                    $hashed_password = $row['PASSWORD'];

                    if (password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        header("Location: index.php");
                        exit();
                    } else {
                        $mess = "Tên đăng nhập hoặc mật khẩu không đúng!";
                    }
                } else {
                    $mess = "Tên đăng nhập hoặc mật khẩu không đúng!";
                }
                $stmt->close();
            }
        }
    ?>
    <div class="login-container">
        <div id="login-form" class="form-container">
            <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username">
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password">
                </div>
                <input type="submit" name="login" value="Đăng nhập">
                <br>
                <span class="error"><?php echo $mess;?></span>
                <a class="text-center" href="forgetpassword.php">Quên mật khẩu?</a>
                <br>
                <div class="form-group">
                    <input type="button" value="Đăng ký" class="register-button" onclick="showRegisterForm()">
                    <input type="button" value="Facebook" class="facebook-button" onclick ="LoginFacebook()">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    mysqli_close($conn);
?>
