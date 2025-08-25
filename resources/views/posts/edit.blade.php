<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>編輯文章</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>編輯文章</h1>
                    <div class="d-flex align-items-center gap-3">
                        <div class="alert alert-info mb-0 py-2">
                            <i class="fas fa-user"></i> 
                            目前登入使用者 ID: <strong id="currentUserId">載入中...</strong>
                        </div>
                        <a href="/posts" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> 返回文章列表
                        </a>
                    </div>
                </div>

                <!-- 載入中提示 -->
                <div id="loading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                    <p class="mt-2">正在載入文章...</p>
                </div>

                <!-- 編輯表單 -->
                <div id="editForm" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <form id="editPostForm">
                                <div class="mb-3">
                                    <label for="title" class="form-label">文章標題 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required maxlength="255">
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
                                                <option value="">請選擇分類</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tag_id" class="form-label">標籤</label>
                                            <select class="form-select" id="tag_id" name="tag_id">
                                                <option value="">請選擇標籤（可選）</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                -->

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" onclick="goBack()">
                                        <i class="fas fa-times"></i> 取消
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save"></i> 更新文章
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 錯誤訊息 -->
                <div id="errorMessage" class="alert alert-danger" style="display: none;">
                    <!-- 錯誤訊息將在這裡顯示 -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 全域變數
        let currentPostId = null;
        let currentPost = null;

        // 顯示登入狀態
        async function displayLoginStatus() {
            const currentUserIdElement = document.getElementById('currentUserId');
            
            if (currentUserIdElement) {
                const accessToken = localStorage.getItem('accessToken');
                
                if (!accessToken) {
                    currentUserIdElement.textContent = '未登入';
                    currentUserIdElement.className = 'text-danger';
                    return;
                }
                
                try {
                    // 從 API 取得使用者資訊
                    const response = await fetch('/api/profile', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + accessToken,
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        currentUserIdElement.textContent = `${data.user.id} (${data.user.username})`;
                        currentUserIdElement.className = 'text-success';
                    } else {
                        currentUserIdElement.textContent = '認證失敗';
                        currentUserIdElement.className = 'text-danger';
                    }
                } catch (error) {
                    currentUserIdElement.textContent = '載入失敗';
                    currentUserIdElement.className = 'text-danger';
                }
            }
        }

        // 頁面載入完成後執行
        document.addEventListener('DOMContentLoaded', function() {
            // 顯示登入狀態
            displayLoginStatus();
            
            // 從 URL 獲取文章 ID
            const urlParts = window.location.pathname.split('/');
            currentPostId = urlParts[urlParts.length - 2]; // /posts/{id}/edit

            if (!currentPostId) {
                showError('文章 ID 無效');
                return;
            }

            // 載入分類和標籤
            // loadCategories();
            // loadTags();
            
            // 載入文章資料
            loadPost();
            
            // 綁定表單提交事件
            document.getElementById('editPostForm').addEventListener('submit', handleSubmit);
        });

        // 載入文章資料
        async function loadPost() {
            showLoading(true);
            hideForm();

            try {
                const response = await fetch(`/api/posts/${currentPostId}`);
                const data = await response.json();

                if (data.success) {
                    currentPost = data.post;
                    displayForm();
                    populateForm();
                } else {
                    showError('載入文章失敗：' + data.message);
                }
            } catch (error) {
                console.error('載入文章失敗:', error);
                showError('載入文章失敗，請檢查網路連線');
            } finally {
                showLoading(false);
            }
        }

        // 載入分類列表
        async function loadCategories() {
            try {
                const response = await fetch('/api/categories');
                const data = await response.json();
                
                if (data.success) {
                    const categorySelect = document.getElementById('category_id');
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.category_id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('載入分類失敗:', error);
            }
        }

        // 載入標籤列表
        async function loadTags() {
            try {
                const response = await fetch('/api/tags');
                const data = await response.json();
                
                if (data.success) {
                    const tagSelect = document.getElementById('tag_id');
                    data.tags.forEach(tag => {
                        const option = document.createElement('option');
                        option.value = tag.tag_id;
                        option.textContent = tag.name;
                        tagSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('載入標籤失敗:', error);
            }
        }

        // 填充表單
        function populateForm() {
            if (!currentPost) return;

            document.getElementById('title').value = currentPost.title;
            document.getElementById('content').value = currentPost.content;
            
            // 檢查分類選擇器是否存在
            const categorySelect = document.getElementById('category_id');
            if (categorySelect) {
                categorySelect.value = currentPost.category_id || '';
            }
            
            // 檢查標籤選擇器是否存在
            const tagSelect = document.getElementById('tag_id');
            if (tagSelect) {
                tagSelect.value = currentPost.tag_id || '';
            }
        }

        // 處理表單提交
        async function handleSubmit(e) {
            e.preventDefault();
            
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            // 檢查分類選擇器是否存在
            const categorySelect = document.getElementById('category_id');
            const categoryId = categorySelect ? categorySelect.value : null;
            
            // 檢查標籤選擇器是否存在
            const tagSelect = document.getElementById('tag_id');
            const tagId = tagSelect ? tagSelect.value : null;

            if (!title || !content) {
                showAlert('請填寫所有必填項目！', 'warning');
                return;
            }

            setLoadingState(true);

            try {
                // 從 localStorage 取得使用者 ID


                const postData = {
                    title: title,
                    content: content,
                    category_id: categoryId ? parseInt(categoryId) : null,
                    tag_id: tagId ? parseInt(tagId) : null,
                };

                const response = await fetch(`/api/posts/${currentPostId}`, {
                    method: 'PUT',
                                    headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('accessToken')
                },
                    body: JSON.stringify(postData)
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('文章更新成功！正在跳轉...', 'success');
                    setTimeout(() => {
                        window.location.href = '/posts';
                    }, 1500);
                } else {
                    showAlert('更新文章失敗：' + data.message, 'danger');
                }
            } catch (error) {
                console.error('更新文章失敗:', error);
                showAlert('更新文章失敗，請檢查網路連線或稍後再試', 'danger');
            } finally {
                setLoadingState(false);
            }
        }

        // 顯示表單
        function displayForm() {
            const editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.style.display = 'block';
            }
        }

        // 隱藏表單
        function hideForm() {
            const editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.style.display = 'none';
            }
        }

        // 顯示載入中
        function showLoading(show) {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.style.display = show ? 'block' : 'none';
            }
        }

        // 設定載入狀態
        function setLoadingState(loading) {
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                if (loading) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 更新中...';
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> 更新文章';
                }
            }
        }

        // 顯示提示訊息
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.firstChild);
            
            // 自動隱藏提示
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // 顯示錯誤訊息
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i> ${message}
                <a href="/posts" class="btn btn-outline-secondary btn-sm ms-3">返回文章列表</a>
            `;
            errorDiv.style.display = 'block';
        }

        // 返回上一頁
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/posts';
            }
        }
    </script>
</body>
</html>
