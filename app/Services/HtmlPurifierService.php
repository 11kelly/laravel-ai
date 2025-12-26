<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlPurifierService
{
    protected HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();

        // 设置缓存目录
        $cacheDir = storage_path('app/purifier');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cacheDir);

        // 允许的 HTML 元素和属性
        $config->set('HTML.Allowed', 'h1,h2,h3,h4,h5,h6,p,br,strong,b,em,i,u,s,a[href|title|target],ul,ol,li,blockquote,pre,code,img[src|alt|width|height|class],table,thead,tbody,tr,th,td,div[class],span[class]');

        // 允许的 URL 协议
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        // 禁止 JavaScript URL
        $config->set('Attr.EnableID', false);

        // 设置目标链接为新窗口打开
        $config->set('HTML.TargetBlank', true);

        // 禁止表单元素
        $config->set('HTML.ForbiddenElements', ['form', 'input', 'button', 'script', 'style', 'iframe', 'object', 'embed']);

        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * 净化 HTML 内容，移除恶意脚本
     */
    public function purify(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        return $this->purifier->purify($html);
    }
}

