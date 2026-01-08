using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerTickContext(
        BattlePointModel BattlePoint,
        BattlePointModel PvpOpponentBattlePoint,
        IReadOnlyList<DeckUnitModel> DeckUnits,
        IReadOnlyList<DeckUnitModel> PvpOpponentDeckUnits,
        IReadOnlyList<CharacterUnitModel> Units,
        IReadOnlyList<SpecialUnitModel> SpecialUnits,
        SpecialUnitSummonInfoModel SpecialUnitSummonInfo,
        SpecialUnitSummonQueueModel SpecialUnitSummonQueue,
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary,
        MstPageModel MstPage,
        IUnitGenerationModelFactory UnitGenerationModelFactory,
        ICoordinateConverter CoordinateConverter,
        CommonConditionContext CommonConditionContext,
        RushModel PvpOpponentRushModel,
        TickCount TickCount)
    {
        public static AutoPlayerTickContext Empty { get; } = new(
            BattlePointModel.Empty,
            BattlePointModel.Empty,
            new List<DeckUnitModel>(),
            new List<DeckUnitModel>(),
            new List<CharacterUnitModel>(),
            new List<SpecialUnitModel>(),
            SpecialUnitSummonInfoModel.Empty,
            SpecialUnitSummonQueueModel.Empty,
            new Dictionary<KomaId, KomaModel>(),
            MstPageModel.Empty,
            null,
            null,
            CommonConditionContext.Empty,
            RushModel.Empty,
            TickCount.Zero
        );

        public BattlePointModel GetBattlePoint(BattleSide battleSide) =>
            battleSide == BattleSide.Player ? BattlePoint : PvpOpponentBattlePoint;

        public IReadOnlyList<DeckUnitModel> GetDeckUnits(BattleSide battleSide) =>
            battleSide == BattleSide.Player ? DeckUnits : PvpOpponentDeckUnits;
    }
}
