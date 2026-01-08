using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public record MangaAnimationUpdateProcessResult(
        IReadOnlyList<MangaAnimationModel> UpdatedMangaAnimationModels,
        IReadOnlyList<MangaAnimationModel> StartingMangaAnimations
    );
}
