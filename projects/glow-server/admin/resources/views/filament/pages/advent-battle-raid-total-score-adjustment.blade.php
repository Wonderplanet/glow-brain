<x-filament-panels::page>
    <x-filament::card>
        <h3>降臨バトル協力スコア調整</h3>
        <p class="text-sm text-gray-600 mt-2">
            降臨バトルの協力スコア報酬判定に使用される協力スコアを調整します。
            <br />
            <span class="text-red-600 font-semibold">※ QA用の機能です。本番環境では使用しないでください。</span>
        </p>

        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
            <h4 class="font-semibold text-sm text-blue-900 mb-2">使用方法</h4>
            <ol class="list-decimal list-inside space-y-1 text-xs text-blue-800">
                <li>降臨バトルIDを選択し、「現在スコア確認」をクリック</li>
                <li>新しいスコアを入力して「スコアを設定」をクリック</li>
                <li>確認ダイアログで内容を確認して実行</li>
            </ol>
        </div>
        <br />
        {{ $this->form }}
    </x-filament::card>

    @if($currentScoreData)
        <x-filament::card>
            <h3>現在スコア情報</h3>
            <table class="table">
                <tr>
                    <th>項目</th>
                    <th>値</th>
                </tr>
                <tbody>
                    <tr>
                        <td>降臨バトルID</td>
                        <td>{{ $currentScoreData['mst_advent_battle_id'] }}</td>
                    </tr>
                    <tr>
                        <td>協力スコア</td>
                        <td class="font-bold text-lg">{{ number_format($currentScoreData['score']) }}</td>
                    </tr>
                    <tr>
                        <td>キャッシュキー</td>
                        <td><code class="bg-gray-100 px-2 py-1 rounded">{{ $currentScoreData['cache_key'] }}</code></td>
                    </tr>
                </tbody>
            </table>
        </x-filament::card>
    @endif
</x-filament-panels::page>
