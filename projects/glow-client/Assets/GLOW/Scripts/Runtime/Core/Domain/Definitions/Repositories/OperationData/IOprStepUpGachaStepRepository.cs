using System.Collections.Generic;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOprStepUpGachaStepRepository
    {
        IReadOnlyList<OprStepUpGachaStepModel> GetOprStepUpGachaModels(MasterDataId oprGachaId);
        OprStepUpGachaStepModel GetOprStepUpGachaStepModelFirstOrDefault(
            MasterDataId oprGachaId,
            StepUpGachaStepNumber stepNumber);
    }
}

