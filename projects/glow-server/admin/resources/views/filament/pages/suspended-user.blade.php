@php
    use App\Constants\UserStatus;
@endphp
<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    <div class="mt-4 space-y-2" style="text-align: right;">
        @foreach ($this->getActionButtons() as $action)
            {{ $action }}
        @endforeach
    </div>
    @if ($this->status !== UserStatus::NORMAL->value)
        <div style="border-radius: 10px; background-color:{{$messageBackgroundColor}}; font-size:5rem; color:#fff;">
            <p>{{$this->message}}</p>
        </div>
    @endif
    {{$this->userInfoList}}
    @if ($this->perpetuallyStopped)
        {{$this->banPermanentInfoList}}
    @endif
    {{$this->banTemporaryInfoList}}
    <x-filament::card>
        <h3 class="font-bold">アカウント停止操作一覧</h3>
        <table class="table">
            <thead>
            <tr>
                <th>操作ステータス</th>
                <th>操作経緯</th>
                <th>操作日時</th>
            </tr>
            </thead>
            <tbody>
                @foreach($this->getAdmUserBanOperateHistoriesTableRows() as $value)
                <tr>
                    <td>{{$value['status']}}</td>
                    <td>{!! $value['operation_reason'] !!}</td>
                    <td>{{$value['operated_at']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
    {{$this->table}}
</x-filament-panels::page>
