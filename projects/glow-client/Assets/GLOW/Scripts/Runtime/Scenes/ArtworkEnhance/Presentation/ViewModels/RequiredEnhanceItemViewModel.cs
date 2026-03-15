using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels
{
    public record RequiredEnhanceItemViewModel(
        PlayerResourceIconViewModel RequiredUpGradeItemIconViewModel,
        ItemAmount PossessionAmount,
        ItemAmount ConsumeAmount);
}
