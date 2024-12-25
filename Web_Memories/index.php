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
    <link rel="stylesheet" href="/web_memories/css/index.css">
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
    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);

        //Lấy ngày hẹn hò
        $mess = $maKH = $ngayhenho = "";
        $sql = "SELECT NGAYHENHO FROM HENHO WHERE ID_HH = (SELECT ID_HH FROM KHACHHANG WHERE ID_TK = ?) AND TRANGTHAI = 1";
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
                $date = new DateTime($ngayhenho);
                $ngayhenho = $date->format('d/m/Y');
                $ngayhenho_tmp = $date->format('Y-m-d');
            }        
        }
        $stmt->close();

        //Tìm người hẹn  hò
        $datayourlove = [];
        if ($id_tk) {
            $datayourlove = TimNguoiHenHo($conn, $email);
        }
        if (!empty($datayourlove) && !empty($datayourlove['NGAYSINH'])) {
            $lovebirthdays[] = date('Y-m-d', strtotime($datayourlove['NGAYSINH']));
        }
        $lovebirthdaysJson = json_encode($lovebirthdays ?? []);

        $datacustomer = [];
        if ($id_tk) {
            $datacustomer = ThongTinKhachHang($conn, $id_tk);
        }
        if (!empty($datacustomer) && !empty($datacustomer['NGAYSINH'])) {
            $birthdays[] = date('Y-m-d', strtotime($datacustomer['NGAYSINH']));
        }
        $birthdaysJson = json_encode($birthdays ?? []);
        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            if (isset($_POST['codelove'])){
                $codelove = trim(filter_input(INPUT_POST, "codelove", FILTER_SANITIZE_SPECIAL_CHARS));
                $ngayyeu = filter_input(INPUT_POST, "datelove", FILTER_SANITIZE_SPECIAL_CHARS);
                $maKH = ThongTinUser($conn, $codelove);
                if(CheckMaKhachHang($conn, $codelove) == 1){
                    if(trim($codelove) == trim($codelove)){
                        $procedure = "CALL SP_SETHENHO(?, ?, ?)";
                        $stmt1 = $conn->prepare($procedure);
                        $stmt1->bind_param('sss', $codelove, $id_tk, $ngayyeu);
                        $stmt1->execute();
                        $stmt1->close(); 
    
                        $_SESSION['form_submitted'] = true;
                    
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit;
                    }
                }
                else{
                    $mess = "ID không tồn tại hoặc người dùng này đang hẹn hò!";
                }
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
        <?php } elseif (!empty($ngayhenho)) { ?>
            <div class="groups-container">
                <div class="group">
                    <div id="profilePicPreview">
                        <?php if (!empty($datacustomer) && !empty($datacustomer['AVARTAR'])): ?>
                            <img src="<?php echo $datacustomer['AVARTAR'];?>" alt="Ảnh đại diện">
                        <?php endif; ?>
                    </div>
                    <div class="profileName">
                        <p><b><?php echo !empty($datacustomer['HOTEN']) ? $datacustomer['HOTEN'] : ''; ?></b></p>
                    </div>
                </div>
                <div class="group">
                    <img src="/web_memories/images/traitim.gif" class="w-25">
                </div>
                <div class="group" >
                    <div id="profilePicPreview">
                        <?php if (!empty($datayourlove) && !empty($datayourlove['AVARTAR'])): ?>
                            <img src="<?php echo $datayourlove['AVARTAR'];?>" alt="Ảnh đại diện">
                        <?php endif; ?>
                    </div>
                    <div class="profileName">
                        <p><b><?php echo !empty($datayourlove['HOTEN']) ? $datayourlove['HOTEN'] : ''; ?></b></p>
                    </div>
                </div>
            </div>
            <br>
            <p>Bạn đang hẹn hò với <span><b><?php echo !empty($datayourlove['HOTEN']) ? $datayourlove['HOTEN'] : ''; ?></b></span></p>
            <p>Ngày yêu: <b><?php echo htmlspecialchars($ngayhenho); ?></b></p>
            <script>
                const startDate = new Date('<?php echo $ngayhenho_tmp; ?>');
                const currentDate = new Date();
                const timeDiff = Math.abs(currentDate.getTime() - startDate.getTime());
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                document.write('<p>Đã yêu nhau được <b>' + daysDiff + '</b> ngày</p>');
            </script>
            <br>
            <div class="group">
                <button id="showCalendarBtn">Xem lịch</button>
                <br>
                <div id="year-selection" class="year-selection" style="display: none;">
                    <label for="year"><b>Chọn năm:</b></label>
                    <select id="year">
                        <!-- Các tùy chọn năm sẽ được thêm động từ JavaScript -->
                    </select>
                </div>
                <br>
                <div id ="date-memories" style="display: none;">
                    <b><label id="datememories" class="text-info"></label></b>
                </div>
                <br>
                <div id="calendar-container" style="display: none;"></div><br>
            </div>
        <?php } else { ?>
            <div class="group-fa">
                <p>Bạn FA bao lâu rồi?Hãy mau kiếm cho mình một người để yêu thương ❤</p>
                <p>Nếu có người yêu rồi thì nhập ID bên dưới nhé 💑</p>
                <form id="accountForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="form-group">
                        <label for="codelove">ID:</label>
                        <input type="text" id="codelove" name="codelove" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Ngày yêu: </label>
                        <input type="date" id="datelove" name="datelove" value="" max="">
                    </div>
                    <span class="error"><?php echo $mess;?></span>
                    <br>
                    <button type="submit" class="btn-submit">Xác nhận</button>
                </form>
            </div>
            <script>
                let today = new Date().toISOString().split('T')[0];
                document.getElementById("datelove").setAttribute("max", today);
            </script>
        <?php } ?>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const calendarContainer = document.getElementById('calendar-container');
    const showCalendarBtn = document.getElementById('showCalendarBtn');
    const yearSelect = document.getElementById('year');
    const yearSelectionDiv = document.getElementById('year-selection');
    const datememories = document.getElementById('date-memories');

    const currentYear = new Date().getFullYear();
    for (let i = currentYear - 25; i <= currentYear + 25; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        yearSelect.appendChild(option);
    }

    yearSelect.value = currentYear;

    function calculateAnniversaries(startDate) {
        const anniversaries = [];
        let date = new Date(startDate);

        for (let i = 100; i <= 1000; i += 100) {
            let anniversary = new Date(date.getTime() + i * 24 * 60 * 60 * 1000);
            anniversaries.push({
                date: anniversary.toISOString().split('T')[0],
                days: i
            });
        }

        return anniversaries;
    }

    const anniversaryStartDate = '<?php echo $ngayhenho_tmp; ?>';
    const anniversaryDates = calculateAnniversaries(anniversaryStartDate);

    const anniversarySet = new Set(anniversaryDates.map(a => a.date));

    const birthdaySet = <?php echo $birthdaysJson; ?>;
    const lovebirthdaySet = <?php echo $lovebirthdaysJson; ?>;

    function getNextAnniversary() {
        const today = new Date();
        let closestAnniversary = null;
        let minDifference = Infinity;
        let anniversaryDays = 0;

        anniversaryDates.forEach(anniversary => {
            const anniversaryDate = new Date(anniversary.date);
            const timeDifference = anniversaryDate - today;
            const daysDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));

            if (daysDifference > 0 && daysDifference < minDifference) {
                closestAnniversary = anniversaryDate;
                minDifference = daysDifference;
                anniversaryDays = anniversary.days;
            }
        });

        return {
            date: closestAnniversary,
            daysDifference: minDifference,
            anniversaryDays: anniversaryDays
        };
    }

    const nextAnniversaryInfo = getNextAnniversary();
    if (nextAnniversaryInfo.date) {
        document.getElementById('datememories').textContent = `Còn ${nextAnniversaryInfo.daysDifference} ngày nữa đến ngày kỷ niệm ${nextAnniversaryInfo.anniversaryDays} ngày (${nextAnniversaryInfo.date.toISOString().split('T')[0]})`;
    } else {
        document.getElementById('datememories').textContent = 'Không có kỷ niệm nào sau ngày hôm nay.';
    }

    function generateCalendar(year) {
        calendarContainer.innerHTML = '';

        const monthsPerRow = 3;
        let rowDiv = document.createElement('div');
        rowDiv.className = 'row';

        const today = new Date();
        const todayDate = today.getDate();
        const todayMonth = today.getMonth();
        const todayYear = today.getFullYear();

        for (let i = 0; i < 12; i++) {
            const month = new Date(year, i, 1);
            const monthName = month.toLocaleString('default', { month: 'long' });

            const monthDiv = document.createElement('div');
            monthDiv.className = 'month';

            const monthHeader = document.createElement('h2');
            monthHeader.textContent = monthName;
            monthDiv.appendChild(monthHeader);

            const monthTable = document.createElement('table');
            monthTable.className = 'month-table';

            const thead = document.createElement('thead');
            const trHead = document.createElement('tr');
            const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            daysOfWeek.forEach(day => {
                const th = document.createElement('th');
                th.textContent = day;
                trHead.appendChild(th);
            });

            thead.appendChild(trHead);
            monthTable.appendChild(thead);

            const tbody = document.createElement('tbody');
            const firstDay = new Date(year, i, 1);
            const lastDay = new Date(year, i + 1, 0);
            const currentDay = new Date(year, i, 1, 0, 0, 0);
            currentDay.setDate(currentDay.getDate() - currentDay.getDay());

            for (let row = 0; row < 6; row++) {
                const tr = document.createElement('tr');
                for (let col = 0; col < 7; col++) {
                    const td = document.createElement('td');
                    if (currentDay.getMonth() === i) {
                        td.textContent = currentDay.getDate();

                        if (currentDay.getDate() === todayDate && currentDay.getMonth() === todayMonth && currentDay.getFullYear() === todayYear) {
                            td.classList.add('today');
                            td.textContent += ' Hôm nay';
                        }

                        const dateString = currentDay.getFullYear() + '-' + ('0' + (currentDay.getMonth() + 1)).slice(-2) + '-' + ('0' + currentDay.getDate()).slice(-2);
                        if (birthdaySet.includes(dateString)) {
                            td.classList.add('birthday');
                            td.textContent += ' 🎂';
                        }

                        if (lovebirthdaySet.includes(dateString)) {
                            td.classList.add('birthday');
                            td.textContent += ' 🎂❤';
                        }

                        if (anniversarySet.has(dateString)) {
                            const anniversary = anniversaryDates.find(a => a.date === dateString);
                            if (anniversary) {
                                td.classList.add('anniversary');
                                td.textContent += ` ❤${anniversary.days}`;
                            }
                        }
                    }
                    tr.appendChild(td);
                    currentDay.setDate(currentDay.getDate() + 1);
                }
                tbody.appendChild(tr);
            }

            monthTable.appendChild(tbody);
            monthDiv.appendChild(monthTable);

            rowDiv.appendChild(monthDiv);

            if ((i + 1) % monthsPerRow === 0) {
                calendarContainer.appendChild(rowDiv);
                rowDiv = document.createElement('div');
                rowDiv.className = 'row';
            }
        }

        if (rowDiv.children.length > 0) {
            calendarContainer.appendChild(rowDiv);
        }
    }

    showCalendarBtn.addEventListener('click', function() {
        if (calendarContainer.style.display === 'none') {
            generateCalendar(parseInt(yearSelect.value));
            calendarContainer.style.display = 'flex';
            yearSelectionDiv.style.display = 'flex';
            datememories.style.display = 'flex';
            showCalendarBtn.textContent = 'Ẩn lịch';
        } else {
            calendarContainer.style.display = 'none';
            yearSelectionDiv.style.display = 'none';
            datememories.style.display = 'none';
            showCalendarBtn.textContent = 'Xem lịch';
        }
    });

    yearSelect.addEventListener('change', function() {
        if (calendarContainer.style.display !== 'none') {
            generateCalendar(parseInt(yearSelect.value));
        }
    });
});
</script>
</body>
</html>
