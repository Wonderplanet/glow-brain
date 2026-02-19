using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeHeaderIconUseCaseModel(
        UnitAssetKey UnitAssetKey,
        EmblemAssetKey EmblemAssetKey);
}
