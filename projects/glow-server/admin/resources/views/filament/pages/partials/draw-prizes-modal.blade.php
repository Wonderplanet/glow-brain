<div class="overflow-x-auto">
    @if(empty($prizes))
        <p class="text-gray-500 dark:text-gray-400">データがありません</p>
    @else
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2">賞品ID</th>
                    <th class="px-4 py-2">報酬</th>
                    <th class="px-4 py-2">獲得数</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prizes as $prize)
                    <tr class="border-b dark:border-gray-600">
                        <td class="px-4 py-2">{{ $prize['prizeId'] }}</td>
                        <td class="px-4 py-2">{{ $prize['reward'] }}</td>
                        <td class="px-4 py-2">{{ $prize['count'] }}/{{ $prize['stock'] }}個</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
