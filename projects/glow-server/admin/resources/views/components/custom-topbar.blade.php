@php
    function getBackgroundColor($env): string
    {
        return match ($env) {
            'develop' => '#90ee90',
            'dev-ld', 'dev_ld' => '#fffacd',
            'dev-qa', 'dev_qa' => '#ffa500',
            'staging' => '#1a90ff',
            'production' => '#dc143c',
            default => '#fff',
        };
    }
@endphp

<div>
    <span class="custom-topbar">
        <span class="info-item"><span class="font-bold">環境</span>: {{$env}}</span><span class="separator">|</span>
        <span class="info-item"><span class="font-bold">release_key</span>: {{$version}}</span><span class="separator">|</span>
        <span class="info-item"><span class="font-bold">サーバー時間</span>: {{$currentDateTime}}</span><span class="separator">|</span>
        <span class="info-item"><span class="font-bold">mstDB</span>: {{$currentMstDatabase}}</span>
    </span>
</div>

<style>
    /** ヘッダーすべての色をかえる */
    div.fi-topbar > nav, .fi-sidebar-header {
         background-color: {{getBackgroundColor($env)}};
    }

    /** カスタムトップバーのスタイリング */
    .custom-topbar {
        display: inline-block;
        padding: 8px 16px;
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 6px;
        font-size: 14px;
        line-height: 1.4;
        white-space: nowrap;
    }

    .custom-topbar .separator {
        margin: 0 12px;
        color: rgba(0, 0, 0, 0.5);
        font-weight: normal;
    }

    .custom-topbar .info-item {
        margin-right: 4px;
    }
</style>
