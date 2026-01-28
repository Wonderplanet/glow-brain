@php
    $tabGroups = $this->getTabGroups();
    $currentTab = $this->getCurrentTab();
    $userId = $this->getUserId();
    $status = $this->getStatus();
    use App\Constants\UserStatus;
@endphp

<div>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    @if ($status !== UserStatus::NORMAL->value)
        <div style="margin-top: 15px; border-radius: 10px; background-color:{{$this->messageBackgroundColor}}; font-size:5rem; color:#fff;">
            <p>
                {{$this->message}}
            </p>
        </div>
    @endif
    <div style="margin-top: 15px;">
        <x-filament::fieldset>
            <table class="table">
                <thead>
                <tr>
                    <th>プレイヤーID</th>
                    <th>MY_ID</th>
                    <th>名前</th>
                    <th>最終ログイン日時</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $userId }}</td>
                    <td>{{ $this->myId }}</td>
                    <td>{{ $this->name }}</td>
                    <td>{{ $this->lastLoginAt }}</td>
                </tr>
                </tbody>
            </table>
            {{$this->usrUserInfoList()}}
            {{$this->billingInfoList()}}
            {{$this->takeoverInfoList()}}
        </x-filament::fieldset>
    </div>
    <div style="margin-top: 15px">
        @if(!$this->isUserDetail)
        <details class="collapse collapse-arrow bg-base-200">
            <summary class="collapse-title text-xl font-medium">ユーザーメニュー</summary>
        @endif
            <div @if(!$this->isUserDetail) class="collapse-content"@endif>
                @foreach($tabGroups as $tabGroup)
                    <ul class="menu xl:menu-horizontal">
                        @foreach($tabGroup as $key => $value)
                            <li>
                                <h2 class="menu-title">{{$key}}</h2>
                                <ul>
                                    @foreach($value as $tabName => $routeName)
                                        <li>
                                            @php $route = route('filament.admin.pages.' . $routeName, ['userId' => $userId]); @endphp
                                            <a class="@if($currentTab === $tabName)active @endif" href="{{$route}}">{{$tabName}}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                    <div class="divider"></div>
                @endforeach
            </div>
        @if(!$this->isUserDetail)
        </details>
        @endif
    </div>
</div>

<x-filament-panels::header
    :actions="$this->getCachedHeaderActions()"
    :heading="$this->getHeading()"
/>
