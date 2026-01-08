using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckSpecialUnitSummonEvaluator : IDeckSpecialUnitSummonEvaluator
    {
        public bool CanSummon(
            DeckUnitModel deckUnit,
            BattlePointModel battlePointModel,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            BattleSide battleSide)
        {
            if (!CanSummonBaseConditions(specialUnitSummonInfo, specialUnits, specialUnitSummonQueue, battleSide)) return false;
            if (deckUnit.IsEmptyUnit()) return false;
            if (deckUnit.RoleType != CharacterUnitRoleType.Special) return false;
            if (deckUnit.IsSummoned) return false;

            return battlePointModel.CurrentBattlePoint >= deckUnit.SummonCost;
        }

        public CanSummonAnySpecialUnitFlag CanSummonBaseConditions(
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            BattleSide battleSide)
        {
            if (!specialUnitSummonInfo.CanSpecialUnitSummonFlag) return CanSummonAnySpecialUnitFlag.False;

            // プレイヤーキャラでスペシャルキャラの召喚位置選択中は召喚できない
            if (specialUnitSummonInfo.IsSummonPositionSelecting &&
                battleSide == BattleSide.Player) return CanSummonAnySpecialUnitFlag.False;

            // スペシャルキャラが召喚中は召喚できない
            if (specialUnits.Any(unit => unit.BattleSide == battleSide)) return CanSummonAnySpecialUnitFlag.False;

            // スペシャルキャラが召喚待ちの場合は召喚できない
            if (specialUnitSummonQueue.SummonQueue
                .Any(unit => unit.BattleSide == battleSide)) return CanSummonAnySpecialUnitFlag.False;

            return CanSummonAnySpecialUnitFlag.True;
        }

        public bool IsSummonablePosition(
            PageCoordV2 pos,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            ICoordinateConverter coordinateConverter,
            BattleSide battleSide)
        {
            // 選択地点が射程の範囲内か
            var fieldCoordTargetPoint = coordinateConverter.PageToFieldCoord(pos);
            var targetKomaNo = mstPage.GetKomaNoAt(fieldCoordTargetPoint);

            return IsSummonableKoma(
                targetKomaNo,
                specialUnitSummonInfoModel,
                mstPage,
                komaDictionary,
                battleSide);
        }

        public bool IsSummonableKoma(
            KomaNo komaNo,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            BattleSide battleSide)
        {
            // 選択地点が射程の範囲内か
            if (!specialUnitSummonInfoModel.KomaRange.IsInRange(komaNo, battleSide)) return false;

            // 配置地点に選択できるコマの状態か
            var targetKoma = mstPage.GetKoma(komaNo);

            if (targetKoma.IsEmpty()) return false;
            if (!komaDictionary.ContainsKey(targetKoma.KomaId)) return false;
            if (!komaDictionary[targetKoma.KomaId].CanSelectAsSpecialUnitSummonTarget()) return false;

            return true;
        }

        /// <summary> スペシャルユニットでの必殺技使用時にコマ選択を必要とする効果範囲のタイプか </summary>
        public NeedTargetSelectTypeFlag NeedsTargetSelection(AttackData attackData)
        {
            var attackRange = attackData.MainAttackElement.AttackRange;

            var isNeedTargetSelect = attackRange.StartPointType != AttackRangePointType.Page &&
                                     attackRange.EndPointType != AttackRangePointType.Page;

            return isNeedTargetSelect ? NeedTargetSelectTypeFlag.True : NeedTargetSelectTypeFlag.False;
        }
    }
}
