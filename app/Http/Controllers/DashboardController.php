<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\BookingServiceInterface;
use App\DTOs\BookingFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __construct(
        private BookingServiceInterface $bookingService
    ) {}

    /**
     * 用户后台首页 - 显示用户的预约
     */
    public function index(Request $request): View
    {
        // 确保用户已登录
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 获取用户的所有预约
        $bookings = $this->bookingService->getUserBookings($user->id, new BookingFilter());

        // 统计信息
        $totalBookings = $bookings->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();
        $pendingBookings = $bookings->where('status', 'pending')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();

        return view('dashboard.index', compact(
            'bookings',
            'totalBookings',
            'confirmedBookings',
            'pendingBookings',
            'cancelledBookings'
        ));
    }

    /**
     * 取消预约
     */
    public function cancelBooking(Request $request, int $bookingId): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['error' => '请先登录'], 401);
            }

            $this->bookingService->cancelBooking($bookingId, $userId);

            return response()->json([
                'success' => true,
                'message' => '预约已成功取消'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 获取用户的预约统计
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['error' => '请先登录'], 401);
            }

            $bookings = $this->bookingService->getUserBookings($userId, new BookingFilter());

            $stats = [
                'total' => $bookings->count(),
                'confirmed' => $bookings->where('status', 'confirmed')->count(),
                'pending' => $bookings->where('status', 'pending')->count(),
                'cancelled' => $bookings->where('status', 'cancelled')->count(),
                'attended' => $bookings->where('status', 'attended')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 显示个人资料页面
     */
    public function profile(): View
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        return view('dashboard.profile', compact('user'));
    }

    /**
     * 更新个人资料
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
        ]);

        // 准备联系信息（邮箱使用登录账号的邮箱）
        $contactInfo = [
            'name' => $validated['contact_name'],
            'email' => $user->email, // 使用登录账号的邮箱
            'address' => $validated['contact_address'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
        ];

        // 确保所有字符串都是UTF-8编码，并移除控制字符
        array_walk_recursive($contactInfo, function (&$value) {
            if (is_string($value)) {
                // 先移除控制字符
                $value = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $value);
                // 然后确保UTF-8编码
                $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, ['UTF-8', 'GBK', 'GB2312', 'BIG5'], true));
            }
        });

        // 更新用户信息
        $updateData = [
            'contact_info' => $contactInfo,
        ];

        try {
            if ($user->updateProfile($updateData)) {
                return redirect()->route('dashboard.profile')->with('success', '个人资料已更新成功！');
            } else {
                return redirect()->route('dashboard.profile')->with('error', '个人资料更新失败，请重试。');
            }
        } catch (\JsonException $e) {
            // 特殊处理JSON编码错误 - 脱敏敏感信息
            $sanitizedContactInfo = $contactInfo;
            if (isset($sanitizedContactInfo['email'])) {
                $sanitizedContactInfo['email'] = '***@***.***';
            }
            if (isset($sanitizedContactInfo['address'])) {
                $sanitizedContactInfo['address'] = substr($sanitizedContactInfo['address'], 0, 3) . '***';
            }

            \Log::error('个人资料JSON编码失败', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'contact_info_sanitized' => $sanitizedContactInfo,
                'raw_data_length' => strlen(json_encode($contactInfo, JSON_UNESCAPED_UNICODE))
            ]);

            return redirect()->route('dashboard.profile')->with('error', '数据格式错误，请检查输入内容后重试。');
        } catch (\Exception $e) {
            // 记录错误详情 - 脱敏敏感信息
            $sanitizedContactInfo = $contactInfo;
            if (isset($sanitizedContactInfo['email'])) {
                $sanitizedContactInfo['email'] = '***@***.***';
            }
            if (isset($sanitizedContactInfo['address'])) {
                $sanitizedContactInfo['address'] = substr($sanitizedContactInfo['address'], 0, 3) . '***';
            }

            \Log::error('个人资料更新失败', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'contact_info_sanitized' => $sanitizedContactInfo
            ]);

            return redirect()->route('dashboard.profile')->with('error', '个人资料更新失败：' . $e->getMessage());
        }
    }

    /**
     * 获取用户个人资料信息（API）
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['error' => '请先登录'], 401);
            }

            $user = auth()->user();

            return response()->json([
                'name' => $user->name,
                'email' => $user->email,
                'contact_info' => $user->contact_info,
                'profile_completed' => $user->hasCompleteProfile()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
