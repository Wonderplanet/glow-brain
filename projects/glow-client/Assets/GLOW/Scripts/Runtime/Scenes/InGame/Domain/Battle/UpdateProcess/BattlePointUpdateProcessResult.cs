using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record BattlePointUpdateProcessResult(
        BattlePointModel UpdatedBpModel,
        BattlePointModel UpdatedOpponentBpModel)
    {
        public static BattlePointUpdateProcessResult Empty { get; } = new (
            BattlePointModel.Empty,
            BattlePointModel.Empty);
    }
}
