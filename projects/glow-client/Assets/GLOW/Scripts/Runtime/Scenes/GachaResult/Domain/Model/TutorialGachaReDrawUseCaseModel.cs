using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaResult.Domain.Model
{
    public record TutorialGachaReDrawUseCaseModel(
        MasterDataId GachaId,
        GachaType GachaType,
        GachaDrawCount DrawCount,
        CostType CostType,
        CostAmount CostAmount,
        MasterDataId CostId);
}
