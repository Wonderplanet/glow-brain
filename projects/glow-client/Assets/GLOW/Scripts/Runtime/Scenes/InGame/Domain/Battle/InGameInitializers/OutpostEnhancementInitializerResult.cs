using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record OutpostEnhancementInitializerResult(
        OutpostEnhancementModel OutpostEnhancement,
        OutpostEnhancementModel PvpOpponentOutpostEnhancement)
    {
        public static OutpostEnhancementInitializerResult Empty { get; } = new (
            OutpostEnhancementModel.Empty,
            OutpostEnhancementModel.Empty);
    }
}
