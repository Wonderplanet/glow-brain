using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public record UnitTransformationAnimationInfo(
        FieldObjectId BeforeUnitId,
        AutoPlayerSequenceElementId BeforeUnitAutoPlayerSequenceElementId,
        CharacterUnitModel AfterUnitModel,
        IReadOnlyList<MangaAnimationModel> MangaAnimationModels);
}
