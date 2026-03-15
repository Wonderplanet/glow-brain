using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Domain.UseCaseModel
{
    public record ArtworkGradeContentCellUseCaseModel(
        ArtworkName ArtworkName,
        IReadOnlyList<PlayerResourceModel> RequiredItemIconViewModels,
        ArtworkGradeLevel RequiredGradeLevel,
        ArtworkGradeLevel TargetGradeLevel,
        ArtworkGradeReleasedFlag IsGradeReleased,
        ArtworkGradeMaxLimitFlag IsGradeMaxLimit);
}
