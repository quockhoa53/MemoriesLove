<section id="top">
    <div class="container">
        <div class="row d-flex justify-content-between align-items-center text-center">
            <div class="col-12 col-sm-4 col-md-3 text-center text-md-left">
                <a href="index.php">
                    <img src="/web_memories/images/logo_memorieslove.gif" class="w-50 ml-auto" alt="Logo Memories Love">
                </a> 
            </div>
            <div class="col-12 col-sm-8 col-md-6">
                <form action="" method="GET">
                    <input type="text" name="query" class="form-control" placeholder="Tìm..." aria-label="Search" required>
                </form>
            </div>
            <div class="col-12 col-sm-8 col-md-3 d-flex">
                <a href="account.php"><i class="bi bi-person-hearts menu-toggle red-icon"></i></a>
                <span><?php echo htmlspecialchars($username); ?></span>
            </div>
        </div>
    </div>
</section>
<hr>
<section id="head">
    <div class="container">
        <nav class="navbar navbar-expand-md navbar-light justify-content-center">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item <?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="index.php"><b>Trang chủ</b></a>
                    </li>
                    <li class="nav-item <?php echo ($currentPage == 'diary.php') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="diary.php"><b>Nhật ký</b></a>
                    </li>
                    <li class="nav-item <?php echo ($currentPage == 'memories.php') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="memories.php"><b>Kỉ niệm</b></a>
                    </li>
                    <li class="nav-item <?php echo ($currentPage == 'game.php') ? 'active' : ''; ?> mr-3">
                        <a class="nav-link" href="game.php"><b>Game</b></a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</section>