<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>創建文章</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">部落格系統</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/posts">文章</a>
                <a class="nav-link active" href="/posts/create">創建文章</a>
                <a class="nav-link" href="/files">檔案</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>創建新文章</h1>
                    <div class="d-flex align-items-center gap-3">
                        <div class="alert alert-info mb-0 py-2">
                            <i class="fas fa-user"></i> 
                            目前登入使用者 ID: <strong id="currentUserId">載入中...</strong>
                        </div>
                        <a href="/posts" class="btn btn-outline-secondary">返回文章列表</a>
                    </div>
                </div>

                <!-- 提示訊息區域 -->
                <div id="alertContainer"></div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <form id="createPostForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="title" class="form-label">文章標題 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">文章內容 <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                                    </div>

                                    <!-- 註解掉分類和標籤選擇
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">分類 <span class="text-danger">*</span></label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">選擇分類</option>
                                                    <option value="1">技術</option>
                                                    <option value="2">生活</option>
                                                    <option value="3">其他</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tag_id" class="form-label">標籤</label>
                                                <select class="form-select" id="tag_id" name="tag_id">
                                                    <option value="">選擇標籤（可選）</option>
                                                    <option value="1">Laravel</option>
                                                    <option value="2">PHP</option>
                                                    <option value="3">Web開發</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    -->

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                            <span id="submitText">創建文章</span>
                                            <span id="loadingSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                提示
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li>• 標題和內容為必填項目</li>
                                    <!-- <li>• 分類必須選擇</li> -->
                                    <!-- <li>• 標籤為可選項目</li> -->
                                    <li>• 創建後可以編輯和刪除</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 顯示登入狀態
        function displayLoginStatus() {
            const currentUserIdElement = document.getElementById('currentUserId');
            
            // 從 localStorage 取得登入資訊
            const isLoggedIn = localStorage.getItem('isLoggedIn');
            const userId = localStorage.getItem('userId');
            const username = localStorage.getItem('username');
            
            if (isLoggedIn && userId) {
                currentUserIdElement.textContent = `${userId} (${username || '未知使用者'})`;
                currentUserIdElement.className = 'text-success';
            } else {
                currentUserIdElement.textContent = '未登入';
                currentUserIdElement.className = 'text-danger';
            }
        }

        // 頁面載入時顯示登入狀態
        displayLoginStatus();

        document.getElementById('createPostForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            // const categoryId = document.getElementById('category_id').value;
            // const tagId = document.getElementById('tag_id').value;

            // 驗證表單
            if (!title || !content) {
                showAlert('請填寫所有必填項目！', 'warning');
                return;
            }

            // 顯示載入狀態
            setLoadingState(true);

            try {
                // 準備資料
                // 從 localStorage 取得使用者 ID
                const userId = localStorage.getItem('userId');
                if (!userId) {
                    showAlert('請先登入！', 'warning');
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 1500);
                    return;
                }

                const postData = {
                    title: title,
                    content: content,
                    // category_id: parseInt(categoryId),
                    // tag_id: tagId ? parseInt(tagId) : null,
                    user_id: userId
                    // status: 'draft'
                };

                // 發送請求到後端
                const response = await fetch('/api/posts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(postData)
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('文章創建成功！正在跳轉...', 'success');
                    // 延遲跳轉到文章列表
                    setTimeout(() => {
                        window.location.href = '/posts';
                    }, 1500);
                } else {
                    if (response.status === 401 && data.redirect) {
                        showAlert('請先登入！正在跳轉到登入頁面...', 'warning');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    } else {
                        showAlert('創建文章失敗：' + data.message, 'danger');
                    }
                }
            } catch (error) {
                console.error('創建文章失敗:', error);
                showAlert('創建文章失敗，請檢查網路連線或稍後再試', 'danger');
            } finally {
                // 隱藏載入狀態
                setLoadingState(false);
            }
        });

        // 顯示提示訊息
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            // 自動隱藏提示（除了成功訊息）
            if (type !== 'success') {
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        }

        // 設置載入狀態
        function setLoadingState(loading) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            if (loading) {
                submitBtn.disabled = true;
                submitText.textContent = '創建中...';
                loadingSpinner.style.display = 'inline-block';
            } else {
                submitBtn.disabled = false;
                submitText.textContent = '創建文章';
                loadingSpinner.style.display = 'none';
            }
        }
    </script>
</body>
</html>
