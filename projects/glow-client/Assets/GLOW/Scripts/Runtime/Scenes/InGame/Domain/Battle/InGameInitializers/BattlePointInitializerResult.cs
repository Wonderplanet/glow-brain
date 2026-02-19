using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record BattlePointInitializerResult(
        BattlePointModel BattlePointModel,
        BattlePointModel PvpOpponentBattlePointModel)
    {
        public static BattlePointInitializerResult Empty { get; } = new (
            BattlePointModel.Empty,
            BattlePointModel.Empty);
    }
}
