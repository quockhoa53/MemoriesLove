<?php
include("include/database.php");
include("include/function.php");
include("include/mail.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memories Love - Forget Password</title>
    <link rel="icon" type="image/gif" href="/web_memories/images/icon.gif">
    <link rel="stylesheet" href="/web_memories/css/login.css">
    <link rel="stylesheet" href="/web_memories/css/all.css">
    <script>
        function showLoginForm() {
            window.location.href = 'login.php';
        }
    </script>
</head>

<body class="body-login">
    <?php
    $mess = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);

        if ($email) {
            $sql = "SELECT USERNAME, PASSWORD FROM TAIKHOAN WHERE EMAIL = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 0) {
                    $mess = 'Email not found!';
                } else {
                    $row = $result->fetch_assoc();
                    $username = $row['USERNAME'];
                    try {
                        $mail->addAddress($email, $username);
                        $rancode = mt_rand(10000000, 99999999);
                        $mail->isHTML(true);
                        $mail->Subject = 'Change your password';
                        $mail->Body    = 'New your password: ' . $rancode;
                        $mail->AltBody = 'New your password: ' . $rancode;

                        if ($mail->send()) {
                            $mess = 'Success, please check the email!';

                            //Cập nhật password
                            $hash = password_hash($rancode, PASSWORD_DEFAULT);
                            $sql_1 = "UPDATE TAIKHOAN SET PASSWORD = ? WHERE EMAIL = ?";
                            $stmt_1 = $conn->prepare($sql_1);

                            if ($stmt_1) {
                                $stmt_1->bind_param("ss", $hash, $email);
                                $stmt_1->execute();
                            }
                        }
                    } catch (Exception $exception) {
                        $mess = "An error has occurred! Mailer Error: {$mail->ErrorInfo}";
                    }
                }
            } else {
                $mess = 'Failed to prepare the SQL statement.';
            }
        } else {
            $mess = 'Invalid email address.';
        }
    }
    ?>

    <div class="login-container">
        <div id="login-form" class="form-container">
            <form class="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="submit" value="Submit">
                    <input type="button" value="Cancel" class="register-button" onclick="showLoginForm()">
                </div>
                <br>
                <span class="error"><?php echo $mess; ?></span>
            </form>
        </div>
    </div>
</body>

</html>
<?php
mysqli_close($conn);
?>