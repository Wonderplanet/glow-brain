using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckSpecialUnitSummonPositionSelector : IDeckSpecialUnitSummonPositionSelector
    {
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] IDeckSpecialUnitSummonPositionShifter DeckSpecialUnitSummonPositionShifter { get; }

        public PageCoordV2 SelectSummonPosition(
            PageCoordV2 pos,
            AttackData attackData,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            ICoordinateConverter coordinateConverter,
            BattleSide battleSide)
        {
            if (DeckSpecialUnitSummonEvaluator.NeedsTargetSelection(attackData))
            {
                // 召喚コマを指定する必要がある場合は、指定位置が召喚可能コマならその位置をそのまま返して、
                // 召喚不可ならEmptyを返す
                var isSummonablePosition = DeckSpecialUnitSummonEvaluator.IsSummonablePosition(
                    pos,
                    specialUnitSummonInfoModel,
                    mstPage,
                    komaDictionary,
                    coordinateConverter,
                    battleSide);

                return isSummonablePosition ? pos : PageCoordV2.Empty;
            }
            else
            {
                // 召喚コマを指定する必要がない場合は、指定位置が召喚可能コマでないなら、一番近い召喚可能コマを召喚位置にする
                return DeckSpecialUnitSummonPositionShifter.ShiftSummonPosition(
                    pos,
                    specialUnitSummonInfoModel,
                    mstPage,
                    komaDictionary,
                    coordinateConverter,
                    battleSide);
            }
        }
    }
}
