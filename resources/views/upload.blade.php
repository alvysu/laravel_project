<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>檔案上傳</title>
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
        .user-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        input[type="file"] {
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
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .logout-btn {
            background-color: #dc3545;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>檔案上傳</h2>
        
        <div class="user-info">
            <p>歡迎，<span id="username"></span>！</p>
            <p>使用者 ID：<span id="userId"></span></p>
        </div>

        <form id="uploadForm">
            <input type="file" name="file" accept="*/*" required>
            <button type="submit">上傳檔案</button>
        </form>
        <button class="btn-secondary" onclick="location.href='{{ route('files') }}'">查看檔案清單</button>
        <button class="btn-secondary" onclick="location.href='{{ route('profile') }}'">個人資料</button>
        <button class="logout-btn" onclick="logout()">登出</button>
    </div>

    <script>
    // 檢查登入狀態並取得使用者資訊
    window.onload = function() {
        const accessToken = localStorage.getItem('accessToken');
        if (!accessToken) {
            alert('請先登入！');
            location.href = '{{ route('login') }}';
            return;
        }
        
        // 從 API 取得使用者資訊
        fetchUserInfo();
    };

    // 從 API 取得使用者資訊
    async function fetchUserInfo() {
        try {
            const response = await fetch('/api/profile', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                document.getElementById('username').textContent = data.user.username;
                document.getElementById('userId').textContent = data.user.id;
            } else {
                // 如果取得使用者資訊失敗，可能是 token 過期
                localStorage.removeItem('accessToken');
                location.href = '{{ route('login') }}';
            }
        } catch (error) {
            console.error('取得使用者資訊失敗:', error);
            localStorage.removeItem('accessToken');
            location.href = '{{ route('login') }}';
        }
    }

    // 登出功能
    async function logout() {
        try {
            const response = await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
                    'Content-Type': 'application/json'
                }
            });
            
            // 清除本地儲存的 token
            localStorage.removeItem('accessToken');
            location.href = '{{ route('login') }}';
        } catch (error) {
            console.error('登出失敗:', error);
            // 即使 API 呼叫失敗，也要清除本地 token
            localStorage.removeItem('accessToken');
            location.href = '{{ route('login') }}';
        }
    }

    // 檔案上傳
    document.getElementById('uploadForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const fileInput = form.querySelector('input[type="file"]');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('請選擇檔案！');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        // user_id 現在由後端 middleware 自動處理
        
        try {
            const res = await fetch('/api/upload', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('accessToken')
                },
                body: formData
            });
            const data = await res.json();
            alert(data.message);
            
            if (data.success) {
                fileInput.value = ''; // 清空檔案選擇
            }
        } catch (error) {
            alert('上傳失敗：' + error.message);
        }
    });
    </script>
</body>
</html> 