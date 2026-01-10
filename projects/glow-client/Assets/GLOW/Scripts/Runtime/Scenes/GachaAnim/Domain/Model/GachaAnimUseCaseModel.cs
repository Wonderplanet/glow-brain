using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GachaAnim.Domain.Model
{
    public record GachaAnimUseCaseModel(
        List<GachaAnimResultModel> GachaAnimResultModels,
        Rarity GachaAnimStartRarity,
        Rarity GachaAnimEndRarity
    );
}
