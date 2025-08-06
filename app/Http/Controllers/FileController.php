<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\MimeTypes;

class FileController extends Controller
{
    /**
     * 處理檔案上傳
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            // 驗證檔案
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 最大 10MB
            ], [
                'file.required' => '請選擇檔案',
                'file.file' => '無效的檔案',
                'file.max' => '檔案大小不能超過 10MB',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileExt = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();

            // 驗證副檔名是否合規
            $mimeTypes = new MimeTypes();
            $validExts = $mimeTypes->getExtensions($mimeType);
            if (!in_array($fileExt, $validExts)) {
                return response()->json([
                    'success' => false,
                    'message' => '副檔名與檔案內容不符，請重新上傳'
                ], 400);
            }

            // 產生 UUID 檔名
            $uuidName = Uuid::uuid4()->toString() . '.' . $fileExt;

            // 儲存檔案到 storage/app/uploads 目錄
            $path = $file->storeAs('uploads', $uuidName, 'local');

            // 從請求中取得使用者 ID（前端會傳送）
            $userId = $request->input('user_id');
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '請先登入'
                ], 401);
            }

            // 寫入資料庫
            File::create([
                'user_id' => $userId,
                'file_path' => $uuidName,
                'upload_time' => now(),
                'post_id' => null, // 暫時設為 null，之後可以關聯到文章
            ]);

            return response()->json([
                'success' => true,
                'message' => '上傳成功！',
                'file_path' => $uuidName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '上傳失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得檔案清單
     */
    public function listFiles(): JsonResponse
    {
        try {
            $files = File::with('user')
                ->orderBy('upload_time', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'files' => $files
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得檔案清單失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 下載檔案
     */
    public function downloadFile(Request $request, $fileId): JsonResponse
    {
        try {
            $file = File::find($fileId);
            
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '檔案不存在'
                ], 404);
            }

            $filePath = storage_path('app/uploads/' . $file->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '檔案不存在於伺服器'
                ], 404);
            }

            // 回傳檔案下載連結
            return response()->json([
                'success' => true,
                'download_url' => '/api/download/' . $fileId,
                'file_name' => $file->file_path
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '下載失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 實際下載檔案
     */
    public function download(Request $request, $fileId)
    {
        try {
            $file = File::find($fileId);
            
            if (!$file) {
                abort(404, '檔案不存在');
            }

            $filePath = storage_path('app/uploads/' . $file->file_path);
            
            if (!file_exists($filePath)) {
                abort(404, '檔案不存在於伺服器');
            }

            return response()->download($filePath, $file->file_path);

        } catch (\Exception $e) {
            abort(500, '下載失敗：' . $e->getMessage());
        }
    }
} 