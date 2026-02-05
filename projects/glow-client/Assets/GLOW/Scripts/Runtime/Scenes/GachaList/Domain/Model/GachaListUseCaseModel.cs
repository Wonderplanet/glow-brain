using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaListUseCaseModel(
        MasterDataId InitialShowOprGachaId,
        GachaListElementUseCaseModel TutorialGachaListElementUseCaseModel,
        IReadOnlyList<GachaListElementUseCaseModel> GachaListUseCaseElementModels)
    {

        public bool HasTargetGacha(GachaType type, MasterDataId targetOprGachaId)
        {
            return GachaListUseCaseElementModels
                .Where(m => m.GachaType == type)
                .Any(m => m.OprGachaId == targetOprGachaId);
        }
    }
}
