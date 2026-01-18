@props([
    'effects' => '',
    'title' => '',
])

<x-filament::card>
    <h3 class="font-semibold">{{ $title }}</h3>
    <table class="table">
        <tr>
            <th>図鑑効果ID</th>
            <th>図鑑効果タイプ</th>
            <th>図鑑効果値</th>
        </tr>
        <tbody>
            @foreach($effects as $effect)
            <tr>
                <td>{{ $effect['mst_encyclopedia_effect_id'] }}</td>
                <td>{{ $effect['effect_type'] }}</td>
                <td>{{ $effect['value'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-filament::card>
