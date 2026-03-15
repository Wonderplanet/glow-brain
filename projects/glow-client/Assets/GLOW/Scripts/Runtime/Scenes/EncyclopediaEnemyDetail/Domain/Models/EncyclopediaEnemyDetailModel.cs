using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Domain.Models
{
    public record EncyclopediaEnemyDetailModel(
        CharacterName Name,
        SeriesLogoImagePath SeriesLogoImagePath,
        UnitDescription Description);
}
