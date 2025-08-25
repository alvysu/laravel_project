<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>檔案清單</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .file-list {
            margin-top: 20px;
        }
        .file-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-info {
            flex: 1;
        }
        .file-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .file-details {
            color: #666;
            font-size: 14px;
        }
        .file-user {
            color: #007bff;
            font-weight: bold;
        }
        .file-time {
            color: #999;
        }
        .file-actions {
            display: flex;
            gap: 10px;
        }
        .btn-download {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-download:hover {
            background-color: #218838;
        }
        .no-files {
            text-align: center;
            color: #666;
            padding: 40px;
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
        .loading {
            text-align: center;
            color: #666;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>檔案清單</h2>
        
        <div id="fileList" class="file-list">
            <div class="loading">載入中...</div>
        </div>
        
        <button class="btn-secondary" onclick="location.href='{{ route('upload') }}'">前往上傳</button>
        <button class="btn-secondary" onclick="location.href='{{ route('login') }}'">返回登入</button>
    </div>

    <script>
    // 下載檔案
    async function downloadFile(fileId, fileName) {
        try {
            const res = await fetch(`/api/download/${fileId}`, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('accessToken')
                }
            });
            
            if (res.ok) {
                // 建立下載連結
                const blob = await res.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } else {
                alert('下載失敗');
            }
        } catch (error) {
            alert('下載失敗：' + error.message);
        }
    }

    // 載入檔案清單
    async function loadFiles() {
        try {
            const res = await fetch('/api/files', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('accessToken')
                }
            });
            const data = await res.json();
            
            const fileList = document.getElementById('fileList');
            
            if (data.success) {
                if (data.files.length === 0) {
                    fileList.innerHTML = '<div class="no-files">目前沒有上傳的檔案</div>';
                } else {
                    fileList.innerHTML = data.files.map(file => `
                        <div class="file-item">
                            <div class="file-info">
                                <div class="file-name">${file.file_path}</div>
                                <div class="file-details">
                                    <span class="file-user">上傳者：${file.user ? file.user.username : '未知'}</span>
                                    <span class="file-time"> | 上傳時間：${new Date(file.upload_time).toLocaleString()}</span>
                                </div>
                            </div>
                            <div class="file-actions">
                                <button class="btn-download" onclick="downloadFile('${file.file_id}', '${file.file_path}')">
                                    下載
                                </button>
                            </div>
                        </div>
                    `).join('');
                }
            } else {
                fileList.innerHTML = '<div class="no-files">載入失敗：' + data.message + '</div>';
            }
        } catch (error) {
            document.getElementById('fileList').innerHTML = '<div class="no-files">載入失敗：' + error.message + '</div>';
        }
    }

    // 頁面載入時執行
    window.onload = function() {
        loadFiles();
    };
    </script>
</body>
</html> 