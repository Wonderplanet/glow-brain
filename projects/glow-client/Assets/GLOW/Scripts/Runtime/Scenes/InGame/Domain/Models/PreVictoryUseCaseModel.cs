using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record PreVictoryUseCaseModel(
        MangaAnimationAssetKey MangaAnimationAssetKey,
        MangaAnimationSpeed AnimationSpeed)
    {
        public static PreVictoryUseCaseModel Empty { get; } = new PreVictoryUseCaseModel(
            MangaAnimationAssetKey.Empty,
            MangaAnimationSpeed.Empty);
    };
}
