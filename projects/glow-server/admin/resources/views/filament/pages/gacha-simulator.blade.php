@php
    $total = 0;
@endphp
<x-filament-panels::page>
    <div>
        <x-filament::breadcrumbs :breadcrumbs="$this->breadcrumbList" />
    </div>
    @if ($status)
        <div style="margin-top: 15px; border-radius: 10px; background-color:{{$this->messageBackgroundColor}}; font-size:5rem; color:#fff;">
            <p>
                {{$this->message}}
            </p>
        </div>
    @endif
    <x-description-list :title="'基本情報'" :list="$this->getBasicInfo()" />
    <x-table :title="'ピックアップキャラ'" :rows="$this->getPickUp()" />
    <x-table :title="'天井情報'" :rows="$this->getUpperTableRows()" />
    <x-table :title="'レアリティ別排出率'" :rows="$this->getRarityProbability()" />

    <x-filament::card>

        <h2 class="font-semibold text-gray-900">シミュレーション</h2>
        <br />
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
        シミュレーション試行回数 :
        <x-filament::input.wrapper style="width: 180px;">
            <x-filament::input
                type="number"
                wire:model.blur="customPlayNum"
                min="{{$this->minimumPlayNum}}"
                max="{{$this->getMaxSimulationCount()}}"
                placeholder="{{$this->minimumPlayNum}}"
            />
        </x-filament::input.wrapper>
        回（必要回数: {{$this->minimumPlayNum}}回）
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
        抽選枠 :
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="prizeType">
            @foreach($this->getPrizeTypeList() as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
        @if (!$hasSimulationData)
            <x-filament::badge color="warning">
                シミュレーション未実行
            </x-filament::badge>
        @elseif ($hasRangeCheckError)
            <x-filament::badge color="danger">
                誤差率エラー
            </x-filament::badge>
        @else
            <x-filament::badge color="success">
                誤差率エラーなし
            </x-filament::badge>
        @endif
        </div>
        <div class="mt-6" style="display: flex;">
            <form style="margin-right: 10px;">
                {{ $this->simulationButton }}
            </form>
            <form>
                {{ $this->simulationReportDownloadButton }}
            </form>
        </div>
        <br />
        <p class="font-semibold text-gray-900">ガシャタイプ : {{$this->oprGachaType}}</p>
        <div wire:key="simulation-table-{{ $prizeType->value }}">
            <table class="table">
                <thead>
                    <td colspan="2"></td>
                    <td colspan="2">設定</td>
                    <td colspan="3">実績</td>
                </thead>
                <tbody>
                    <tr>
                        <td>No.</td>
                        <td>アイテム名</td>
                        <td>レアリティ</td>
                        <td>提供割合（％）</td>
                        <td>排出率（％）</td>
                        <td>誤差率（％）<br />※30％以内</td>
                        <td>排出数</td>
                    </tr>
                    @foreach($simulationResults as $simulationResult)
                        @php
                            $total += $simulationResult['emissionsNum'];
                        @endphp
                        <tr style="background-color: <?php echo ($simulationResult['rangeCheck']) ? 'red' : ''; ?>;">
                            <td>{{$loop->iteration}}</td>
                            <td>{{$simulationResult['itemName']}}</td>
                            <td>{{$simulationResult['rarity']}}</td>
                            <td>{{$simulationResult['provisionRate']}}</td>
                            <td>{{$simulationResult['actualEmissionRate']}}</td>
                            <td>{{$simulationResult['errorRate']}}</td>
                            <td>{{$simulationResult['emissionsNum']}}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>合計</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{$total}}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::card>

</x-filament-panels::page>
