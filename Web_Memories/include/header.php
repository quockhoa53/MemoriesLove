<header id="top" class="header-main">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-12 col-sm-4 col-md-3 text-center text-md-left">
                <a href="index.php" aria-label="Go to Home">
                    <img src="/web_memories/images/logo_memorieslove.gif" class="w-50 ml-auto" alt="Memories Love Logo">
                </a> 
            </div>
            <div class="col-12 col-sm-8 col-md-6 justify-content-end align-items-center">
                <form action="" method="GET" aria-label="Search Form">
                    <input type="text" name="query" class="form-control search-bar" placeholder="Tìm kiếm..." aria-label="Search">
                </form>
            </div>
            <div class="col-12 col-sm-8 col-md-3 d-flex justify-content-end align-items-center">
                <a href="account.php" class="user-icon" aria-label="User Account">
                    <i class="bi bi-person-circle menu-toggle red-icon rotating-icon"></i>
                </a>
                <span class="username">Hi, <?php echo htmlspecialchars($username); ?></span>
            </div>
        </div>
    </div>
    <nav id="head" class="navbar navbar-expand-md navbar-light">
    <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item <?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?>">
                    <a class="nav-link" href="index.php"><b>Trang chủ</b></a>
                </li>
                <li class="nav-item <?php echo ($currentPage == 'diary.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="diary.php"><b>Nhật ký</b></a>
                </li>
                <li class="nav-item <?php echo ($currentPage == 'journey.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="journey.php"><b>Hành trình yêu</b></a>
                </li>
                <li class="nav-item <?php echo ($currentPage == 'memories.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="memories.php"><b>Kỉ niệm</b></a>
                </li>
                <li class="nav-item <?php echo ($currentPage == 'game.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="game.php"><b>Game</b></a>
                </li>
            </ul>
        </div>
    </div>
</nav>
</header>
