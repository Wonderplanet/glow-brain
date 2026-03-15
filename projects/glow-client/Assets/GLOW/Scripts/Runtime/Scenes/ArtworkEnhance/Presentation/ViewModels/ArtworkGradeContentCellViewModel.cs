using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record ArtworkGradeContentCellViewModel(
        ArtworkName ArtworkName,
        IReadOnlyList<PlayerResourceIconViewModel> RequiredItemIconViewModels,
        ArtworkGradeLevel RequiredGradeLevel,
        ArtworkGradeLevel TargetGradeLevel,
        ArtworkGradeReleasedFlag IsGradeReleased,
        ArtworkGradeMaxLimitFlag IsGradeMaxLimit);
}
