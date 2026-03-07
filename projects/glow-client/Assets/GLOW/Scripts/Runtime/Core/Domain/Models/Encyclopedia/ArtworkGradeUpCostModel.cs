using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Encyclopedia
{
    public record ArtworkGradeUpCostModel(
        MasterDataId Id,
        MasterDataId ResourceId,
        ItemAmount ResourceAmount);
}
