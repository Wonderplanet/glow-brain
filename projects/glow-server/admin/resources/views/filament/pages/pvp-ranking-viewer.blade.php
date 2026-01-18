<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    <x-filament::card>
        <h3>ランクマッチランキング閲覧</h3>
        <br />
        {{ $this->form }}
    </x-filament::card>

    @if($userRankingData)
        <x-filament::card>
            <h3>ユーザーランキング情報</h3>
            @if(isset($userRankingData['message']))
                <p class="text-red-600">{{ $userRankingData['message'] }}</p>
            @else
                <table class="table">
                    <tr>
                        <th>項目</th>
                        <th>値</th>
                    </tr>
                    <tbody>
                        <tr>
                            <td>シーズンID</td>
                            <td>{{ $userRankingData['sys_pvp_season_id'] }}</td>
                        </tr>
                        <tr>
                            <td>ユーザーID</td>
                            <td>{{ $userRankingData['usr_user_id'] }}</td>
                        </tr>
                        <tr>
                            <td>ユーザー名</td>
                            <td>{{ $userRankingData['user_name'] }}</td>
                        </tr>
                        <tr>
                            <td>スコア</td>
                            <td>{{ $userRankingData['score'] }}</td>
                        </tr>
                        <tr>
                            <td>ランク</td>
                            <td>{{ $userRankingData['rank'] ?? '圏外' }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </x-filament::card>
    @endif

    @if($rankingTtlData)
        <x-filament::card>
            <h3>ランキングキャッシュTTL情報</h3>
            <table class="table">
                <tr>
                    <th>項目</th>
                    <th>値</th>
                </tr>
                <tbody>
                    <tr>
                        <td>シーズンID</td>
                        <td>{{ $rankingTtlData['sys_pvp_season_id'] }}</td>
                    </tr>
                    <tr>
                        <td>キャッシュキー</td>
                        <td>{{ $rankingTtlData['cache_key'] }}</td>
                    </tr>
                    <tr>
                        <td>TTL（秒）</td>
                        <td>{{ $rankingTtlData['ttl_seconds'] }}</td>
                    </tr>
                    <tr>
                        <td>TTL（表示形式）</td>
                        <td>{{ $rankingTtlData['ttl_formatted'] }}</td>
                    </tr>
                </tbody>
            </table>
        </x-filament::card>
    @endif

</x-filament-panels::page>
