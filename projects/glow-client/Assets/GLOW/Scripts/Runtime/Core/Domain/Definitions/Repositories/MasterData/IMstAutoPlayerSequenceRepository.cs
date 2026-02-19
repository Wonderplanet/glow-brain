using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstAutoPlayerSequenceRepository
    {
        MstAutoPlayerSequenceModel GetMstAutoPlayerSequence(AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId);
#if GLOW_DEBUG
        void RefreshSequenceElementModelCache(IReadOnlyList<MstAutoPlayerSequenceElementModel> debugModels);
        void AddEnemyStageParameterModel(MstEnemyStageParameterModel mstDebugEnemyStageParameterModel);
#endif
    }
}
