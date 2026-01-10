using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class DeckAutoPlayerProcessor : IAutoPlayerProcessor
    {
        readonly BattleSide _battleSide;
        readonly IDeckAutoPlayerSummonSelector _summonSelector;
        readonly IDeckUnitSummonEvaluator _deckUnitSummonEvaluator;
        readonly IDeckSpecialUnitSummonEvaluator _deckSpecialUnitSummonEvaluator;
        readonly IDeckUnitSpecialAttackEvaluator _deckUnitSpecialAttackEvaluator;

        public AutoPlayerSequenceGroupModel CurrentAutoPlayerSequenceGroupModel => AutoPlayerSequenceGroupModel.Empty;
        public AutoPlayerSequenceSummonCount BossCount => AutoPlayerSequenceSummonCount.Empty;

        // アラートの秒数(2秒)
        readonly TickCount _alertTime = new TickCount(100);

        public DeckAutoPlayerProcessor(
            IDeckAutoPlayerSummonSelector summonSelector,
            IDeckUnitSummonEvaluator deckUnitSummonEvaluator,
            IDeckSpecialUnitSummonEvaluator deckSpecialUnitSummonEvaluator,
            IDeckUnitSpecialAttackEvaluator deckUnitSpecialAttackEvaluator,
            BattleSide battleSide)
        {
            _summonSelector = summonSelector;
            _deckUnitSummonEvaluator = deckUnitSummonEvaluator;
            _deckSpecialUnitSummonEvaluator = deckSpecialUnitSummonEvaluator;
            _deckUnitSpecialAttackEvaluator = deckUnitSpecialAttackEvaluator;
            _battleSide = battleSide;
        }

        public IReadOnlyList<IAutoPlayerAction> Tick(AutoPlayerTickContext context)
        {
            var actions = new List<IAutoPlayerAction>();

            actions.Add(UpdateOpponentExecuteRush(context));
            actions.AddRange(CreateSummonActions(context));
            actions.AddRange(CreateSpecialAttackActions(context));

            return actions;
        }

        public bool RemainsSummonUnitByOutpostDamage()
        {
            return false;
        }

        List<IAutoPlayerAction> CreateSummonActions(AutoPlayerTickContext context)
        {
            var deckUnits = context.GetDeckUnits(_battleSide);
            var specialUnits = context.SpecialUnits;
            var battlePointModel = context.GetBattlePoint(_battleSide);
            var specialUnitSummonInfo = context.SpecialUnitSummonInfo;
            var specialUnitSummonQueue = context.SpecialUnitSummonQueue;

            var actions = new List<IAutoPlayerAction>();
            var selectedUnit = GetSummonDeckUnitSelection(deckUnits);

            if (selectedUnit.IsEmpty())
            {
                _summonSelector.UpdateSummonState(selectedUnit, false);
                return actions;
            }

            if (selectedUnit.RoleType == CharacterUnitRoleType.Special)
            {
                var canSummon = _deckSpecialUnitSummonEvaluator.CanSummon(
                    selectedUnit,
                    battlePointModel,
                    specialUnitSummonInfo,
                    specialUnits,
                    specialUnitSummonQueue,
                    _battleSide);
                if (!canSummon)
                {
                    _summonSelector.UpdateSummonState(selectedUnit, false);
                    return actions;
                }

                // スペシャルキャラの召喚位置を取得
                var summonPosition = GetSpecialUnitSummonPosition(
                    context.Units,
                    specialUnitSummonInfo,
                    context.MstPage,
                    context.KomaDictionary,
                    context.CoordinateConverter,
                    _battleSide);

                // 召喚すべき位置がない場合は召喚キャンセル
                if (summonPosition.IsEmpty())
                {
                    _summonSelector.UpdateSummonState(selectedUnit, false);
                    return actions;
                }

                // スペシャルキャラ召喚
                var index = deckUnits.IndexOf(selectedUnit);
                actions.Add(new AutoPlayerSummonDeckSpecialUnitAction(new DeckUnitIndex(index), summonPosition));
            }
            else
            {
                // コスト不足などで召喚不可ならキャンセル
                if (!_deckUnitSummonEvaluator.CanSummon(selectedUnit, battlePointModel))
                {
                    _summonSelector.UpdateSummonState(selectedUnit, false);
                    return actions;
                }

                var index = deckUnits.IndexOf(selectedUnit);
                actions.Add(new AutoPlayerSummonDeckUnitAction(new DeckUnitIndex(index)));
            }

            // 次フレーム以降にスキップしたロールが選択可能になる可能性があるため、インデックスは選択可能になったタイミングで更新する
            _summonSelector.UpdateSummonState(selectedUnit, true);
            return actions;
        }

        List<IAutoPlayerAction> CreateSpecialAttackActions(AutoPlayerTickContext context)
        {
            var deckUnits = context.GetDeckUnits(_battleSide);

            var actions = new List<IAutoPlayerAction>();

            for (int i = 0; i < deckUnits.Count; i++)
            {
                if (_deckUnitSpecialAttackEvaluator.CanUseSpecialAttack(deckUnits[i]))
                {
                    actions.Add(new AutoPlayerDeckSpecialAttackAction(new DeckUnitIndex(i)));
                }
            }

            return actions;
        }

        DeckUnitModel GetSummonDeckUnitSelection(IReadOnlyList<DeckUnitModel> deckUnits)
        {
            var summoningCount = deckUnits.Count(deckUnit => deckUnit.IsSummoned);

            var summonCandidateDeckUnits = deckUnits
                .Where(IsSummonCandidate)
                .ToList();

            return _summonSelector.GetSummonDeckUnit(
                summonCandidateDeckUnits,
                summoningCount);
        }

        /// <summary>
        /// 召喚候補のキャラか
        /// </summary>
        bool IsSummonCandidate(DeckUnitModel deckUnit)
        {
            if (deckUnit.IsEmptyUnit()) return false;
            if (deckUnit.IsSummoned) return false;
            if (!deckUnit.RemainingSummonCoolTime.IsZero() && deckUnit.RoleType != CharacterUnitRoleType.Special) return false;

            return true;
        }

        PageCoordV2 GetSpecialUnitSummonPosition(
            IReadOnlyList<CharacterUnitModel> units,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            ICoordinateConverter coordinateConverter,
            BattleSide battleSide)
        {
            // 自陣営側のキャラを前線から順に並べる
            var sortedUnits = units
                .Where(unit => unit.BattleSide == battleSide)
                .OrderByDescending(unit => unit.Pos.X);

            // 前線に近い方からスペシャルキャラを召喚できるコマか判定
            var prevCheckedKomaId = KomaId.Empty;

            foreach (var unit in sortedUnits)
            {
                if (unit.LocatedKoma.Id == prevCheckedKomaId) continue;
                prevCheckedKomaId = unit.LocatedKoma.Id;

                var komaNo = mstPage.GetKomaNo(unit.LocatedKoma.Id);

                if (!_deckSpecialUnitSummonEvaluator.IsSummonableKoma(
                    komaNo,
                    specialUnitSummonInfo,
                    mstPage,
                    komaDictionary,
                    unit.BattleSide))
                {
                    continue;
                }

                var fieldCoord = coordinateConverter.OutpostToFieldCoord(unit.BattleSide, unit.Pos);
                var pageCoord = coordinateConverter.FieldToPageCoord(fieldCoord);

                return pageCoord;
            }

            return PageCoordV2.Empty;
        }

        IAutoPlayerAction UpdateOpponentExecuteRush(AutoPlayerTickContext context)
        {
            var playerUnitCount = context.Units.Count(unit => unit.BattleSide == BattleSide.Player);

            if (playerUnitCount > 0 &&
                context.PvpOpponentRushModel.CanExecuteRushFlag)
            {
                return AutoPlayerOpponentRushExecuteAction.True;
            }
            else
            {
                return AutoPlayerOpponentRushExecuteAction.False;
            }
        }
    }
}
