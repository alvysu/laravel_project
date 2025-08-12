<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章列表</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">部落格系統</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/posts">文章</a>
                <a class="nav-link" href="/posts/create">創建文章</a>
                <a class="nav-link" href="/files">檔案</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>文章列表</h1>
                    <a href="/posts/create" class="btn btn-primary">創建文章</a>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">歡迎使用文章系統</h5>
                                <p class="card-text">這是一個簡單的文章列表頁面。您可以點擊上方的「創建文章」按鈕來創建新文章。</p>
                                <p class="card-text">目前還沒有文章，請先創建一些文章。</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                快速操作
                            </div>
                            <div class="card-body">
                                <a href="/posts/create" class="btn btn-primary w-100 mb-2">創建文章</a>
                                <a href="/" class="btn btn-outline-secondary w-100">返回首頁</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
