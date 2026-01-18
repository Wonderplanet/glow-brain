using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record RushInitializerResult(
        RushModel RushModel,
        RushModel PvpOpponentRushModel)
    {
        public static RushInitializerResult Empty { get; } = new (
            RushModel.Empty,
            RushModel.Empty);
    }
}
