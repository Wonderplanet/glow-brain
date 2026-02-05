using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.BattleResult.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record PreFinishUseCaseModel(
        MangaAnimationAssetKey MangaAnimationAssetKey,
        MangaAnimationSpeed AnimationSpeed,
        PvpResultModel PvpResultModel)
    {
        public static PreFinishUseCaseModel Empty { get; } = new PreFinishUseCaseModel(
            MangaAnimationAssetKey.Empty,
            MangaAnimationSpeed.Empty,
            PvpResultModel.Empty);
    };
}
