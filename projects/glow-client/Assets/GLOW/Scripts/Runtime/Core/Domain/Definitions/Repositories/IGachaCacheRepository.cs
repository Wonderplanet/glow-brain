using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Repositories
{
    public interface IGachaCacheRepository
    {
        IReadOnlyList<GachaResultModel> GetGachaResultModels();
        void SaveGachaResultModels(IReadOnlyList<GachaResultModel> gachaDrawResultModel);
        void ClearGachaResultModels();
        IReadOnlyList<GachaResultModel> GetStepRewardModels();
        void SaveStepRewardModels(IReadOnlyList<GachaResultModel> stepRewardModels);
        void ClearStepRewardModels();
        GachaDrawInfoModel GetGachaDrawInfoModel();
        void SaveGachaDrawType(GachaDrawInfoModel gachaDrawInfoModel);
        void ClearGachaDrawType();
    }
}
