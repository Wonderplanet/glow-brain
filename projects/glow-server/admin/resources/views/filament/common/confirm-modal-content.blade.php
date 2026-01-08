<table class="text-left">
    @foreach($fields as $column => $name)
        <tr>
            <th >{{$name}}</th>
            <td class="{{(empty($inputs[$column]) && !in_array($column, $requiredColumns)) ? 'text-danger-600' : ''}}" style="{{!empty($inputs[$column]) ? 'color: blue' : ''}}">
                {{!empty($inputs[$column]) ? $inputs[$column] : '未入力'}}
            </td>
        </tr>
    @endforeach
</table>
