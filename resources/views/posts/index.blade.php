<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章列表</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">部落格系統</a>
            <div class="navbar-nav">
                <a class="nav-link active" href="/posts">文章</a>
                <a class="nav-link" href="/posts/create">創建文章</a>
                <a class="nav-link" href="/upload">上傳</a>
                <a class="nav-link" href="/files">檔案</a>
            </div>
            <div class="navbar-nav ms-auto">
                <button class="btn btn-outline-light btn-sm" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> 登出
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>文章列表</h1>
                    <div class="d-flex align-items-center gap-3">
                        <div class="alert alert-info mb-0 py-2">
                            <i class="fas fa-user"></i> 
                            目前登入使用者 ID: <strong id="currentUserId">載入中...</strong>
                        </div>
                        <a href="/posts/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 創建文章
                        </a>
                    </div>
                </div>

                <!-- 搜尋和篩選區域 -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="searchForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">搜尋文章</label>
                                <input type="text" class="form-control" id="search" placeholder="輸入標題或內容關鍵字...">
                            </div>
                            <div class="col-md-3">
                                <label for="categoryFilter" class="form-label">分類</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">全部分類</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="tagFilter" class="form-label">標籤</label>
                                <select class="form-select" id="tagFilter">
                                    <option value="">全部標籤</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> 搜尋
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 文章列表區域 -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- 載入中提示 -->
                        <div id="loading" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">載入中...</span>
                            </div>
                            <p class="mt-2">正在載入文章...</p>
                        </div>

                        <!-- 文章列表 -->
                        <div id="postsList">
                            <!-- 文章將在這裡動態載入 -->
                        </div>

                        <!-- 分頁控制 -->
                        <nav id="pagination" class="mt-4" style="display: none;">
                            <ul class="pagination justify-content-center">
                                <!-- 分頁按鈕將在這裡動態生成 -->
                            </ul>
                        </nav>

                        <!-- 無文章提示 -->
                        <div id="noPosts" class="text-center py-5" style="display: none;">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">目前還沒有文章</h4>
                            <p class="text-muted">點擊上方的「創建文章」按鈕來創建第一篇文章吧！</p>
                            <a href="/posts/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> 創建文章
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-info-circle"></i> 快速操作
                            </div>
                            <div class="card-body">
                                <a href="/posts/create" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-plus"></i> 創建文章
                                </a>
                                <a href="/" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-home"></i> 返回首頁
                                </a>
                            </div>
                        </div>

                        <!-- 統計資訊 -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <i class="fas fa-chart-bar"></i> 統計資訊
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 id="totalPosts">0</h5>
                                        <small class="text-muted">總文章數</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 id="currentPage">1</h5>
                                        <small class="text-muted">當前頁面</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 全域變數
        let currentPage = 1;
        let currentCategory = '';
        let currentTag = '';
        let currentSearch = '';

        // 取得目前登入使用者的 ID
        async function getCurrentUserId() {
            const accessToken = localStorage.getItem('accessToken');
            if (!accessToken) return null;
            
            try {
                const response = await fetch('/api/profile', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + accessToken,
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                return data.success ? data.user.id : null;
            } catch (error) {
                console.error('取得使用者 ID 失敗:', error);
                return null;
            }
        }

        // 頁面載入完成後執行
        document.addEventListener('DOMContentLoaded', function() {
            // 顯示登入狀態
            displayLoginStatus();
            
            // 暫時移除這些功能，因為 API 路由尚未實現
            // loadCategories();
            // loadTags();
            loadPosts();
            
            // 綁定搜尋表單事件
            document.getElementById('searchForm').addEventListener('submit', function(e) {
                e.preventDefault();
                currentSearch = document.getElementById('search').value.trim();
                currentPage = 1;
                loadPosts();
            });

            // 綁定篩選器變更事件
            document.getElementById('categoryFilter').addEventListener('change', function() {
                currentCategory = this.value;
                currentPage = 1;
                loadPosts();
            });

            document.getElementById('tagFilter').addEventListener('change', function() {
                currentTag = this.value;
                currentPage = 1;
                loadPosts();
            });
        });

        // 載入分類列表
        async function loadCategories() {
            try {
                const response = await fetch('/api/categories');
                const data = await response.json();
                
                if (data.success) {
                    const categorySelect = document.getElementById('categoryFilter');
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
                    const tagSelect = document.getElementById('tagFilter');
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

        // 載入文章列表
        async function loadPosts() {
            showLoading(true);
            hideAllContent();

            try {
                // 構建查詢參數
                const params = new URLSearchParams({
                    page: currentPage,
                    per_page: 10
                });

                if (currentCategory) params.append('category_id', currentCategory);
                if (currentTag) params.append('tag_id', currentTag);
                if (currentSearch) params.append('search', currentSearch);

                const response = await fetch(`/api/posts?${params}`);
                const data = await response.json();

                if (data.success) {
                    displayPosts(data.posts);
                    displayPagination(data.pagination);
                    updateStats(data.pagination);
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

        // 顯示文章列表
        function displayPosts(posts) {
            const postsList = document.getElementById('postsList');
            
            if (posts.length === 0) {
                document.getElementById('noPosts').style.display = 'block';
                return;
            }

            let html = '';
            posts.forEach(post => {
                const createdDate = new Date(post.created_time).toLocaleDateString('zh-TW');
                const contentPreview = post.content.length > 150 ? 
                    post.content.substring(0, 150) + '...' : post.content;

                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="/posts/${post.posts_id}" class="text-decoration-none">
                                        ${post.title}
                                    </a>
                                </h5>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary">${post.status || 'draft'}</span>
                                    ${post.user_id === getCurrentUserId() ? 
                                        `<a href="/posts/${post.posts_id}/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> 編輯
                                        </a>` : ''
                                    }
                                </div>
                            </div>
                            
                            <p class="card-text text-muted">${contentPreview}</p>
                            
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex flex-wrap gap-2">
                                        ${post.category_name ? `<span class="badge bg-primary">${post.category_name}</span>` : ''}
                                        ${post.tag_name ? `<span class="badge bg-info">${post.tag_name}</span>` : ''}
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> ${post.author_name || '未知作者'}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> ${createdDate}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            postsList.innerHTML = html;
            postsList.style.display = 'block';
        }

        // 顯示分頁控制
        function displayPagination(pagination) {
            const paginationElement = document.getElementById('pagination');
            
            if (pagination.last_page <= 1) {
                paginationElement.style.display = 'none';
                return;
            }

            let html = '';
            
            // 上一頁按鈕
            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${pagination.current_page - 1})">上一頁</a></li>`;
            }

            // 頁碼按鈕
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === pagination.current_page ? 'active' : '';
                html += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="goToPage(${i})">${i}</a></li>`;
            }

            // 下一頁按鈕
            if (pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${pagination.current_page + 1})">下一頁</a></li>`;
            }

            paginationElement.querySelector('ul').innerHTML = html;
            paginationElement.style.display = 'block';
        }

        // 跳轉到指定頁面
        function goToPage(page) {
            currentPage = page;
            loadPosts();
            // 滾動到頁面頂部
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // 更新統計資訊
        function updateStats(pagination) {
            document.getElementById('totalPosts').textContent = pagination.total;
            document.getElementById('currentPage').textContent = pagination.current_page;
        }

        // 顯示載入中
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        // 隱藏所有內容
        function hideAllContent() {
            document.getElementById('postsList').style.display = 'none';
            document.getElementById('pagination').style.display = 'none';
            document.getElementById('noPosts').style.display = 'none';
        }

        // 顯示錯誤訊息
        function showError(message) {
            const postsList = document.getElementById('postsList');
            postsList.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                </div>
            `;
            postsList.style.display = 'block';
        }

        // 顯示登入狀態
        async function displayLoginStatus() {
            const currentUserIdElement = document.getElementById('currentUserId');
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
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        currentUserIdElement.textContent = `${data.user.id} (${data.user.username})`;
                        currentUserIdElement.className = 'text-success';
                    } else {
                        currentUserIdElement.textContent = '認證失敗';
                        currentUserIdElement.className = 'text-warning';
                    }
                } else {
                    currentUserIdElement.textContent = '認證失敗';
                    currentUserIdElement.className = 'text-warning';
                }
            } catch (error) {
                console.error('取得使用者資訊失敗:', error);
                currentUserIdElement.textContent = '連線失敗';
                currentUserIdElement.className = 'text-warning';
            }
        }

        // 登出函數
        async function logout() {
            try {
                // 呼叫登出 API
                const response = await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
                        'Content-Type': 'application/json'
                    }
                });
                
                // 清除 localStorage 中的登入狀態
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('userId');
                localStorage.removeItem('username');
                localStorage.removeItem('accessToken');
                
                // 跳轉到登入頁面
                window.location.href = '/login';
            } catch (error) {
                console.error('登出失敗:', error);
                // 即使 API 失敗，也要清除本地狀態
                localStorage.clear();
                window.location.href = '/login';
            }
        }
    </script>
</body>
</html>
