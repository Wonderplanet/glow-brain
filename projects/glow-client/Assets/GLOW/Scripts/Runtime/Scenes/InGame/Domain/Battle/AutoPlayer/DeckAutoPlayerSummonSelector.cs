using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class DeckAutoPlayerSummonSelector : IDeckAutoPlayerSummonSelector
    {
        class SummonState
        {
            public bool IsSpecialUnitExists { get; }
            public bool IsPrevSummonSpecial { get; private set; }
            public SummonStateType SummonStateType { get; private set; }
            public int CurrentRoleSummonOrderIndex { get; private set; }
            public int NextRoleSummonOrderIndex { get; set; }

            public SummonState(bool isSpecialUnitExists)
            {
                IsSpecialUnitExists = isSpecialUnitExists;
                SummonStateType = SummonStateType.FirstSummon;
                IsPrevSummonSpecial = false;
                CurrentRoleSummonOrderIndex = 0;
            }

            public void UpdateSummonState(
                DeckUnitModel selectedUnit,
                bool isUnitSummoned)
            {
                if (selectedUnit.IsEmpty())
                {
                    if (SummonStateType == SummonStateType.SecondSummon)
                    {
                        // 2回目の召喚で該当ロールが無い場合はEmptyが返ってくる。その場合2回目の召喚は召喚済みとしてスキップとする
                        NextSummonState();
                    }
                }
                else if (isUnitSummoned)
                {
                    // 召喚が実行された場合は、召喚状態を更新
                    NextSummonState();
                    IsPrevSummonSpecial = selectedUnit.RoleType == CharacterUnitRoleType.Special;
                    CurrentRoleSummonOrderIndex = NextRoleSummonOrderIndex;
                }

                // 召喚の実行・不実行に関わらず同一にする
                NextRoleSummonOrderIndex = CurrentRoleSummonOrderIndex;
            }

            public void ResetOrderIndex()
            {
                CurrentRoleSummonOrderIndex = 0;
                NextRoleSummonOrderIndex = 0;
            }

            void NextSummonState()
            {
                if (SummonStateType == SummonStateType.FirstSummon)
                {
                    SummonStateType = SummonStateType.SecondSummon;
                }
                else if (SummonStateType == SummonStateType.SecondSummon)
                {
                    SummonStateType = SummonStateType.NormalSummon;
                }
            }
        }

        enum SummonStateType
        {
            FirstSummon,
            SecondSummon,
            NormalSummon
        }

        // 高コスト召喚およびスペシャルユニット召喚可能の閾値となる値
        const int SummonThresholdForHighCost = 4;

        // 現在召喚中のユニットが特定数以下の場合に呼び出すロールごとの召喚順
        static readonly IReadOnlyList<CharacterUnitRoleType> SummonRoleOrderList = new List<CharacterUnitRoleType>
        {
            CharacterUnitRoleType.Attack,
            CharacterUnitRoleType.Defense,
            CharacterUnitRoleType.Support,
            CharacterUnitRoleType.Defense,
            CharacterUnitRoleType.Technical
        };

        // バトル開始時の1体目に呼び出すロールを優先度順に並べたもの
        static readonly IReadOnlyDictionary<CharacterUnitRoleType, DeckAutoPlayerSummonPriority> FirstSummonRolePriorities =
            new Dictionary<CharacterUnitRoleType, DeckAutoPlayerSummonPriority>
        {
            {CharacterUnitRoleType.Attack, new DeckAutoPlayerSummonPriority(5)},
            {CharacterUnitRoleType.Defense, new DeckAutoPlayerSummonPriority(4)},
            {CharacterUnitRoleType.Technical, new DeckAutoPlayerSummonPriority(3)},
            {CharacterUnitRoleType.Support, new DeckAutoPlayerSummonPriority(2)},
        };

        // バトル開始時の2体目に呼び出すロールを優先度順に並べたもの
        static readonly IReadOnlyList<CharacterUnitRoleType> SecondSummonRolePriorities = new List<CharacterUnitRoleType>
        {
            CharacterUnitRoleType.Defense,
            CharacterUnitRoleType.Technical,
            CharacterUnitRoleType.Support
        };

        // 高コスト召喚での第二条件でのロール優先度
        static readonly IReadOnlyDictionary<CharacterUnitRoleType, DeckAutoPlayerSummonPriority> HighCostSummonRolePriorities =
            new Dictionary<CharacterUnitRoleType, DeckAutoPlayerSummonPriority>
        {
            {CharacterUnitRoleType.Attack, new DeckAutoPlayerSummonPriority(5)},
            {CharacterUnitRoleType.Technical, new DeckAutoPlayerSummonPriority(4)},
            {CharacterUnitRoleType.Defense, new DeckAutoPlayerSummonPriority(3)},
            {CharacterUnitRoleType.Support, new DeckAutoPlayerSummonPriority(2)},
        };

        readonly SummonState _summonState;
        readonly IReadOnlyList<CharacterUnitRoleType> _currentDeckRoleSummonOrderList;

        public DeckAutoPlayerSummonSelector(IReadOnlyList<DeckUnitModel> deckUnits)
        {
            bool isSpecialUnitExists = deckUnits.Any(unit => unit.RoleType == CharacterUnitRoleType.Special);
            _summonState = new SummonState(isSpecialUnitExists);

            // 召喚順に呼び出す場合のListから現在の編成に存在しないロールを除外
            var availableRoles = deckUnits
                .Select(unit => unit.RoleType)
                .Distinct()
                .ToHashSet();

            _currentDeckRoleSummonOrderList = SummonRoleOrderList
                .Where(role => availableRoles.Contains(role))
                .ToList();
        }

        public void UpdateSummonState(DeckUnitModel selectedUnit, bool isUnitSummoned)
        {
            // 召喚状態を更新
            _summonState.UpdateSummonState(selectedUnit, isUnitSummoned);
        }

        public DeckUnitModel GetSummonDeckUnit(
            IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits,
            int summoningCount)
        {
            switch (_summonState.SummonStateType)
            {
                case SummonStateType.FirstSummon:
                    return GetFirstSummonDeckUnit(summonCandidateDeckUnits);
                case SummonStateType.SecondSummon:
                    return GetSecondSummonDeckUnit(summonCandidateDeckUnits);
                case SummonStateType.NormalSummon:
                default:
                    return GetNormalSummonDeckUnit(
                        summonCandidateDeckUnits,
                        summoningCount,
                        _summonState.CurrentRoleSummonOrderIndex,
                        _summonState.IsPrevSummonSpecial,
                        _summonState.IsSpecialUnitExists);
            }
        }

        public DeckUnitModel GetFirstSummonDeckUnit(IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits)
        {
            // バトル開始時用の最初の召喚優先順に呼び出すユニットを選択
            // 第一優先をコストの低い順にし、第二優先をロールごとの召喚優先順にする
            // スペシャルユニットのみの編成の場合はEmptyが返る形
            return summonCandidateDeckUnits
                .Where(unit => unit.RoleType != CharacterUnitRoleType.Special)
                .OrderBy(unit => unit.SummonCost)
                .ThenByDescending(unit => FirstSummonRolePriorities.GetValueOrDefault(
                    unit.RoleType,
                    DeckAutoPlayerSummonPriority.One))
                .FirstOrDefault(DeckUnitModel.Empty);
        }

        public DeckUnitModel GetSecondSummonDeckUnit(IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits)
        {
            // バトル開始時用の2体目の召喚優先順に呼び出すユニットを選択
            foreach (var role in SecondSummonRolePriorities)
            {
                if (summonCandidateDeckUnits.All(unit => unit.RoleType != role)) continue;

                // ロールが一致した中からコストが低いユニットを一人選出
                var selectUnit = summonCandidateDeckUnits
                    .Where(unit => unit.RoleType == role)
                    .MinBy(unit => unit.SummonCost);

                return selectUnit;
            }

            return DeckUnitModel.Empty;
        }

        public DeckUnitModel GetNormalSummonDeckUnit(
            IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits,
            int summoningCount,
            int currentRoleSummonOrderIndex,
            bool isPrevSummonSpecial,
            bool isSpecialUnitExists)
        {
            var orderIndex = currentRoleSummonOrderIndex;

            // 召喚中ユニットが4体以上になったタイミングでインデックスをリセットする
            if (SummonThresholdForHighCost <= summoningCount)
            {
                orderIndex = 0;
                _summonState.ResetOrderIndex();
            }

            if (SummonThresholdForHighCost > summoningCount)
            {
                // 召喚中ユニットが3体以下であれば指定の順番で召喚。
                // 該当ロールがいない場合は次のロールに移り、インデックスもそこに合わせる
                // 手動召喚が挟まってもインデックスはそのままにしておく
                var summonInOrder = GetSummonDeckUnitInOrder(
                    summonCandidateDeckUnits,
                    orderIndex);

                // 召喚実行した場合の次のインデックス値を保存。キャンセルした場合は反映されない
                _summonState.NextRoleSummonOrderIndex = summonInOrder.nextRoleSummonOrderIndex;

                return summonInOrder.selectedUnit;
            }
            else
            {
                // 召喚中が4体以上になったタイミングでスペシャルキャラを召喚。以降は別のロール→スペシャルキャラ→別のロールのループとなる
                return GetAlternatingSpecialAndHighCostSummon(
                    summonCandidateDeckUnits,
                    isPrevSummonSpecial,
                    isSpecialUnitExists);
            }
        }

        (DeckUnitModel selectedUnit, int nextRoleSummonOrderIndex) GetSummonDeckUnitInOrder(
            IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits,
            int currentRoleSummonOrderIndex)
        {
            // 現在のインデックスを元に順番に召喚していく。念の為範囲外指定されないよう調整
            var orderIndex = currentRoleSummonOrderIndex % _currentDeckRoleSummonOrderList.Count;

            // 先にdeckUnitsをコストの低い順に並び替えておく
            summonCandidateDeckUnits = summonCandidateDeckUnits
                .OrderBy(unit => unit.SummonCost)
                .ToList();

            for (int i = 0; i < _currentDeckRoleSummonOrderList.Count; i++)
            {
                var role = _currentDeckRoleSummonOrderList[orderIndex];
                orderIndex = (orderIndex + 1) % _currentDeckRoleSummonOrderList.Count; // 次のインデックスに進む（最後まで行ったら0に戻る)

                if (summonCandidateDeckUnits.Any(unit => unit.RoleType == role))
                {
                    // ロールが一致した中からコストが低いユニットを一人選出
                    var selectedUnit = summonCandidateDeckUnits.FirstOrDefault(unit => unit.RoleType == role);
                    currentRoleSummonOrderIndex = orderIndex;
                    return (selectedUnit, currentRoleSummonOrderIndex);
                }
            }

            // 条件に合うロールがない場合はEmptyを返し、インデックス値はそのまま
            return (DeckUnitModel.Empty, currentRoleSummonOrderIndex);
        }

        DeckUnitModel GetAlternatingSpecialAndHighCostSummon(
            IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits,
            bool isPrevSummonSpecial,
            bool isSpecialUnitExists)
        {
            if (!isSpecialUnitExists)
            {
                // スペシャルユニットが存在しない場合高コストのユニットを優先的に召喚する
                return GetHighCostSummon(summonCandidateDeckUnits);
            }

            if (isPrevSummonSpecial)
            {
                // 高コストのユニットを優先的に召喚する
                return GetHighCostSummon(summonCandidateDeckUnits);
            }
            else
            {
                // 前回召喚したのがスペシャルユニットでない場合はスペシャルユニットを召喚する
                var selectUnit = summonCandidateDeckUnits
                    .Where(unit => unit.RoleType == CharacterUnitRoleType.Special)
                    .MaxBy(unit => unit.SummonCost) ?? DeckUnitModel.Empty;

                return selectUnit;
            }
        }

        DeckUnitModel GetHighCostSummon(IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits)
        {
            // 第一優先条件としてコストの高いユニット、第二優先条件としてアタック⇨テクニカル⇨ディフェンス⇨サポートの順で優先。
            // コストも同じ場合でロールも同じ場合はレアリティの高い順。
            var maxPriorityUnit = summonCandidateDeckUnits
                .Where(unit => unit.RoleType != CharacterUnitRoleType.Special)
                .OrderByDescending(unit => unit.SummonCost)
                .ThenByDescending(unit => HighCostSummonRolePriorities.GetValueOrDefault(
                    unit.RoleType,
                    DeckAutoPlayerSummonPriority.One))
                .ThenByDescending(unit => unit.Rarity)
                .FirstOrDefault(DeckUnitModel.Empty);

            if (maxPriorityUnit == DeckUnitModel.Empty)
            {
                // ユニットがいない場合はsそのままEmptyを返す
                return DeckUnitModel.Empty;
            }

            // 最大コストのユニットが複数いる場合はその中からランダムに選出
            var maxPriorityUnits = summonCandidateDeckUnits
                .Where(unit => unit.RoleType != CharacterUnitRoleType.Special)
                .Where(unit => unit.SummonCost == maxPriorityUnit.SummonCost)
                .Where(unit => unit.RoleType == maxPriorityUnit.RoleType)
                .Where(unit => unit.Rarity == maxPriorityUnit.Rarity)
                .ToList();
            var unitIndex = Random.Range(0, maxPriorityUnits.Count);
            var selectUnit = maxPriorityUnits[unitIndex];
            return selectUnit;
        }
    }
}
