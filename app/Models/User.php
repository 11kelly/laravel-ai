<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_info',
        'profile_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'contact_info' => 'array',
            'profile_completed' => 'boolean',
        ];
    }

    /**
     * Set the contact_info attribute
     */
    public function setContactInfoAttribute($value)
    {
        if (is_array($value)) {
            // 简化的字符清理：只移除基本的控制字符，避免ReDoS攻击
            array_walk_recursive($value, function (&$item) {
                if (is_string($item)) {
                    // 移除基本控制字符（排除换行符和制表符）
                    $item = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $item);
                    // 确保UTF-8编码
                    $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item, ['UTF-8', 'GBK', 'GB2312', 'BIG5'], true) ?: 'UTF-8');
                }
            });
        }

        // 手动编码为JSON字符串，确保UTF-8兼容性
        $this->attributes['contact_info'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the contact_info attribute
     */
    public function getContactInfoAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return $decoded ?: [];
        } catch (\JsonException $e) {
            \Log::warning('Failed to decode contact_info JSON', [
                'user_id' => $this->id,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 检查用户个人资料是否完整
     */
    public function hasCompleteProfile(): bool
    {
        return $this->profile_completed &&
               !empty($this->contact_info) &&
               !empty($this->contact_info['name']);
    }

    /**
     * 获取用于预约的联系信息
     */
    public function getBookingContactInfo(): array
    {
        return [
            'name' => $this->contact_info['name'] ?? $this->name,
            'email' => $this->contact_info['email'] ?? $this->email,
            'address' => $this->contact_info['address'] ?? null,
        ];
    }

    /**
     * 更新用户个人资料
     */
    public function updateProfile(array $data): bool
    {
        $this->fill($data);

        // 检查是否完整（基于新数据）
        $this->profile_completed = !empty($data['contact_info'] ?? $this->contact_info) &&
                                   !empty(($data['contact_info'] ?? $this->contact_info)['name']);

        return $this->save();
    }

    /**
     * 检查用户是否有预约记录
     */
    public function hasBookings(): bool
    {
        return $this->bookings()->exists();
    }

    /**
     * 获取用户的预约数量
     */
    public function getBookingsCount(): int
    {
        return $this->bookings()->count();
    }

    /**
     * 获取用户的活动预约关系
     */
    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class);
    }

    /**
     * 检查是否可以删除用户
     * 只有没有预约记录的用户才能被删除
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasBookings();
    }
}
