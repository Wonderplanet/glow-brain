<x-filament-panels::page>
    <div class="space-y-6">
        <!-- フォーム -->
        <div class="filament-card p-6">
            {{ $this->form }}
        </div>

        <!-- ユニットサマリー情報 -->
        @if(!empty($unitSummaryData))
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 所持ユニット情報 -->
                <div class="filament-card p-6">
                    <h3 class="text-lg font-semibold mb-4">所持ユニット情報</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600">総ユニット数</div>
                            <div class="text-2xl font-bold text-blue-900">{{ $unitSummaryData['total_units'] ?? 0 }}</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600">現在の総グレード</div>
                            <div class="text-2xl font-bold text-purple-900">{{ $unitSummaryData['current_total_grade'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- グレード差異の確認 -->
                <div class="filament-card p-6">
                    <h3 class="text-lg font-semibold mb-4">グレード差異確認</h3>
                    @php
                        $currentGrade = $unitSummaryData['current_total_grade'] ?? 0;
                        $calculatedGrade = $unitSummaryData['calculated_total_grade'] ?? 0;
                        $difference = $calculatedGrade - $currentGrade;
                    @endphp
                    
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>現在の総グレード:</span>
                            <span class="font-mono">{{ number_format($currentGrade) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>計算後の総グレード:</span>
                            <span class="font-mono">{{ number_format($calculatedGrade) }}</span>
                        </div>
                        <hr>
                        <div class="flex justify-between font-semibold {{ $difference > 0 ? 'text-green-600' : ($difference < 0 ? 'text-red-600' : 'text-gray-600') }}">
                            <span>差異:</span>
                            <span class="font-mono">{{ $difference > 0 ? '+' : '' }}{{ number_format($difference) }}</span>
                        </div>
                    </div>

                    @if($difference != 0)
                        <div class="mt-4 p-3 rounded-lg {{ $difference > 0 ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                            <div class="text-sm">
                                @if($difference > 0)
                                    計算後のグレードが高くなります。サマリーの再構築を推奨します。
                                @else
                                    計算後のグレードが低くなります。データに不整合がある可能性があります。
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ユニット詳細一覧 -->
            @if(!empty($unitSummaryData['unit_details']))
                <div class="filament-card p-6">
                    <h3 class="text-lg font-semibold mb-4">ユニット詳細 ({{ count($unitSummaryData['unit_details']) }}種類)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">ユニットID</th>
                                    <th class="px-3 py-2 text-left">ユニット名</th>
                                    <th class="px-3 py-2 text-left">最高グレード</th>
                                    <th class="px-3 py-2 text-left">最高レベル</th>
                                    <th class="px-3 py-2 text-left">最高ランク</th>
                                    <th class="px-3 py-2 text-left">所持数</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($unitSummaryData['unit_details'] as $unit)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 font-mono">{{ $unit['mst_unit_id'] }}</td>
                                        <td class="px-3 py-2">{{ $unit['unit_name'] }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Grade {{ $unit['max_grade_level'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">Lv.{{ $unit['max_level'] }}</td>
                                        <td class="px-3 py-2">★{{ $unit['max_rank'] }}</td>
                                        <td class="px-3 py-2">{{ $unit['total_count'] }}体</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
