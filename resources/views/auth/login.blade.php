<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
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
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>登入</h2>
        <form id="loginForm">
            <input type="text" name="username" placeholder="帳號" required>
            <input type="password" name="password" placeholder="密碼" required>
            <button type="submit">登入</button>
        </form>
        <button class="btn-secondary" onclick="location.href='{{ route('register') }}'">還沒有帳號？註冊</button>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = {
            username: form.username.value,
            password: form.password.value
        };
        
        try {
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            alert(data.message);
            
            // 登入成功自動跳轉到上傳頁面，並保存登入狀態
            if (data.success) {
                // 保存 API token 到 localStorage（用於後續 API 請求）
                localStorage.setItem('accessToken', data.access_token);
                
                location.href = '/posts';
            }
        } catch (error) {
            alert('登入失敗：' + error.message);
        }
    });
    </script>
</body>
</html> 