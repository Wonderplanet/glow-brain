using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckSpecialUnitSummonEvaluator
    {
        bool CanSummon(
            DeckUnitModel deckUnit,
            BattlePointModel battlePointModel,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            BattleSide battleSide);

        CanSummonAnySpecialUnitFlag CanSummonBaseConditions(
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            SpecialUnitSummonQueueModel specialUnitSummonQueue,
            BattleSide battleSide);

        bool IsSummonablePosition(
            PageCoordV2 pos,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            ICoordinateConverter coordinateConverter,
            BattleSide battleSide);

        bool IsSummonableKoma(
            KomaNo komaNo,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            BattleSide battleSide);

        NeedTargetSelectTypeFlag NeedsTargetSelection(AttackData attackData);
    }
}
