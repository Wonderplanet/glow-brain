<x-filament-panels::page>
    {{-- スケジュール情報 --}}
    <x-filament::card>
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">スケジュール情報</h2>
            
            @if($mstComebackBonusSchedule)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">スケジュールID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $mstComebackBonusSchedule->id }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">非アクティブ条件日数</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $mstComebackBonusSchedule->inactive_condition_days }}日</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">継続日数</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $mstComebackBonusSchedule->duration_days }}日</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">開始日時</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $mstComebackBonusSchedule->start_at }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">終了日時</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $mstComebackBonusSchedule->end_at }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-red-600">スケジュール情報が見つかりません。</p>
            @endif
        </div>
    </x-filament::card>

    {{-- ボーナス一覧 --}}
    <x-filament::card>
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">カムバックボーナス一覧</h2>
            
            @if($comebackBonusRewards && count($comebackBonusRewards) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ログイン日数
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    報酬グループID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    報酬アイテム
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($comebackBonusRewards as $bonusReward)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $bonusReward['login_day_count'] }}日目
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $bonusReward['reward_group_id'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($bonusReward['rewards'] && count($bonusReward['rewards']) > 0)
                                            <div class="space-y-1">
                                                @foreach($bonusReward['rewards'] as $reward)
                                                    @if($reward)
                                                        <x-reward-info :rewardInfo="$reward" />
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-500">報酬情報なし</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-600">このスケジュールに関連するカムバックボーナスはありません。</p>
            @endif
        </div>
    </x-filament::card>

    {{-- 戻るボタン --}}
    <div class="mt-6">
        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.mst-comeback-bonus-schedules.index') }}"
            color="gray"
        >
            ← 一覧に戻る
        </x-filament::button>
    </div>
</x-filament-panels::page>
