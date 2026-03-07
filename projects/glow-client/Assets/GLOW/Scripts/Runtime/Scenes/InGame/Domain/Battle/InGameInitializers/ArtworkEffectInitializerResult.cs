using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record ArtworkEffectInitializerResult(
        ArtworkEffectModel ArtworkEffectModel,
        ArtworkEffectModel PvpOpponentArtworkEffectModel)
    {
        public static ArtworkEffectInitializerResult Empty { get; } = new (
            ArtworkEffectModel.Empty,
            ArtworkEffectModel.Empty);
    }
}
