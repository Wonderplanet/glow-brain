using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IMangaAnimationUpdateProcess
    {
        MangaAnimationUpdateProcessResult UpdateMangaAnimations(
            IReadOnlyList<MangaAnimationModel> mangaAnimationModels,
            IReadOnlyList<CharacterUnitModel> units,
            TickCount tickCount);
    }
}
