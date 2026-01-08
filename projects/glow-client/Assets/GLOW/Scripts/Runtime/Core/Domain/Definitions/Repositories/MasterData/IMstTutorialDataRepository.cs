using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstTutorialRepository
    {
        IReadOnlyList<MstTutorialTipModel> GetMstTutorialTipModels(MasterDataId tutorialTipId);
        IReadOnlyList<MstTutorialModel> GetMstTutorialModels();
    }
}
