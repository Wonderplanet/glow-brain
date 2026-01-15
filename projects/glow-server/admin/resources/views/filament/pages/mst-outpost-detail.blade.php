<x-filament-panels::page>
    <div class="prose prose-lg">
        <x-filament::fieldset>
            <x-slot name="label">
                基本情報
            </x-slot>
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>開始日</th>
                    <th>終了日</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ $outpost->id }}</td>
                    <td>{{ $outpost->start_at }}</td>
                    <td>{{ $outpost->end_at }}</td>
                </tr>
                </tbody>
            </table>
        </x-filament::fieldset>
        <div>
            @foreach($outpost->mst_outpost_enhancement as $enhancement)
                <h3>{{$enhancement->mst_outpost_enhancement_i18n->name}}</h3>
                <span class="text-sm"> [ID: {{$enhancement->id}}] [type: {{$enhancement->outpost_enhancement_type}}]</span>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>レベル</th>
                            <th>必要コイン</th>
                            <th>強化値</th>
                            <th>説明</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enhancement->mst_outpost_enhancement_level as $level)
                            <tr>
                                <td>{{ $level->id }}</td>
                                <td>{{ $level->level }}</td>
                                <td>{{ $level->cost_coin }}</td>
                                <td>{{ $level->enhancement_value }}</td>
                                <td>{{ $level->mst_outpost_enhancement_level_i18n->description }}</td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
