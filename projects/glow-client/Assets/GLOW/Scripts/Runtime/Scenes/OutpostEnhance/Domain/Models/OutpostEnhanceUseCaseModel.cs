using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhance.Domain.Models
{
    public record OutpostEnhanceUseCaseModel(
        Coin UserCoin,
        HP OutpostHp,
        IReadOnlyList<OutpostEnhanceTypeButtonModel> Buttons);
}
