using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.ViewModels
{
    public record EncyclopediaEnemyDetailViewModel(
        CharacterName Name,
        SeriesLogoImagePath SeriesLogoImagePath,
        UnitDescription Description);
}
