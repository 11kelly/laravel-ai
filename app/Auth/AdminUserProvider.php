<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Admin User Provider
 * 
 * 自定义用户提供器，仅允许 role='admin' 的用户通过后台认证
 * 确保前后台认证完全隔离
 */
final class AdminUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     * 
     * 只返回 admin 角色的用户
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $user = parent::retrieveById($identifier);
        
        if ($user && $user->role === 'admin') {
            return $user;
        }
        
        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * 
     * 只返回 admin 角色的用户
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        $user = parent::retrieveByToken($identifier, $token);
        
        if ($user && $user->role === 'admin') {
            return $user;
        }
        
        return null;
    }

    /**
     * Retrieve a user by the given credentials.
     * 
     * 只返回 admin 角色的用户
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable
    {
        $user = parent::retrieveByCredentials($credentials);
        
        if ($user && $user->role === 'admin') {
            return $user;
        }
        
        return null;
    }
}

