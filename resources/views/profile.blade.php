<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人資料</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .section h3 {
            margin-top: 0;
            color: #007bff;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 8px 0;
        }
        button:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .loading {
            text-align: center;
            color: #666;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>個人資料</h2>
        
        <div id="userInfo" class="user-info">
            <div class="loading">載入中...</div>
        </div>

        <!-- 更新個人資料 -->
        <div class="section">
            <h3>更新個人資料</h3>
            <form id="updateProfileForm">
                <input type="text" name="username" placeholder="使用者名稱" required>
                <input type="email" name="email" placeholder="信箱" required>
                <button type="submit">更新資料</button>
            </form>
        </div>

        <!-- 修改密碼 -->
        <div class="section">
            <h3>修改密碼</h3>
            <form id="changePasswordForm">
                <input type="password" name="current_password" placeholder="目前密碼" required>
                <input type="password" name="new_password" placeholder="新密碼" required>
                <input type="password" name="confirm_password" placeholder="確認新密碼" required>
                <button type="submit">修改密碼</button>
            </form>
        </div>

        <!-- 刪除帳號 -->
        <div class="section">
            <h3>刪除帳號</h3>
            <form id="deleteAccountForm">
                <input type="password" name="password" placeholder="輸入密碼確認刪除" required>
                <button type="submit" class="btn-danger">刪除帳號</button>
            </form>
        </div>

        <button class="btn-secondary" onclick="location.href='{{ route('upload') }}'">返回上傳</button>
        <button class="btn-secondary" onclick="logout()">登出</button>
    </div>

    <script>
    // 檢查登入狀態
    window.onload = function() {
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        if (!isLoggedIn) {
            alert('請先登入！');
            location.href = '{{ route('login') }}';
            return;
        }
        loadProfile();
    };

    // 載入個人資料
    async function loadProfile() {
        try {
            const userId = localStorage.getItem('userId');
            if (!userId) {
                alert('請先登入！');
                location.href = '{{ route('login') }}';
                return;
            }

            const res = await fetch('/api/profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_id: userId })
            });
            const data = await res.json();
            
            if (data.success) {
                const userInfo = document.getElementById('userInfo');
                userInfo.innerHTML = `
                    <p><strong>使用者名稱：</strong>${data.user.username}</p>
                    <p><strong>信箱：</strong>${data.user.email}</p>
                `;
                
                // 填入表單
                document.querySelector('input[name="username"]').value = data.user.username;
                document.querySelector('input[name="email"]').value = data.user.email;
            } else {
                alert('載入個人資料失敗：' + data.message);
            }
        } catch (error) {
            alert('載入個人資料失敗：' + error.message);
        }
    }

    // 更新個人資料
    document.getElementById('updateProfileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const userId = localStorage.getItem('userId');
        
        if (!userId) {
            alert('請先登入！');
            location.href = '{{ route('login') }}';
            return;
        }
        
        const payload = {
            user_id: userId,
            username: form.username.value,
            email: form.email.value
        };
        
        try {
            const res = await fetch('/api/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            alert(data.message);
            
            if (data.success) {
                loadProfile(); // 重新載入個人資料
            }
        } catch (error) {
            alert('更新失敗：' + error.message);
        }
    });

    // 修改密碼
    document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const userId = localStorage.getItem('userId');
        
        if (!userId) {
            alert('請先登入！');
            location.href = '{{ route('login') }}';
            return;
        }
        
        const payload = {
            user_id: userId,
            current_password: form.current_password.value,
            new_password: form.new_password.value,
            confirm_password: form.confirm_password.value
        };
        
        try {
            const res = await fetch('/api/profile/change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            alert(data.message);
            
            if (data.success) {
                form.reset(); // 清空表單
            }
        } catch (error) {
            alert('修改密碼失敗：' + error.message);
        }
    });

    // 刪除帳號
    document.getElementById('deleteAccountForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const userId = localStorage.getItem('userId');
        
        if (!userId) {
            alert('請先登入！');
            location.href = '{{ route('login') }}';
            return;
        }
        
        if (!confirm('確定要刪除帳號嗎？此操作無法復原！')) {
            return;
        }
        
        const payload = {
            user_id: userId,
            password: form.password.value
        };
        
        try {
            const res = await fetch('/api/profile/delete-account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            alert(data.message);
            
            if (data.success) {
                logout();
            }
        } catch (error) {
            alert('刪除帳號失敗：' + error.message);
        }
    });

    // 登出功能
    function logout() {
        fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(() => {
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('userId');
            localStorage.removeItem('username');
            location.href = '{{ route('login') }}';
        });
    }
    </script>
</body>
</html>
