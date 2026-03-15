using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.OutpostEnhance.Domain.Models
{
    public record OutpostEnhanceUseCaseModel(
        Coin UserCoin,
        HP OutpostHp,
        HP TotalArtworkBonusHp,
        TotalArtworkCount TotalArtworkCount,
        IReadOnlyList<OutpostEnhanceTypeButtonModel> Buttons);
}
