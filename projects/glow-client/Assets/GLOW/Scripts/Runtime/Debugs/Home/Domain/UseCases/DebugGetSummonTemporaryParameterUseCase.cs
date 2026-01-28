#if GLOW_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Debugs.Home.Domain.Constants;

namespace GLOW.Debugs.Home.Domain.UseCases
{
    public class DebugGetUnitTemporaryParameterUseCase
    {
        public IReadOnlyList<MstEnemyStageParameterModel> GetDummySummons()
        {
            return DebugMstUnitTemporaryParameterDefinitions.DebugEnemyStageParameterModels;
        }
    }
}
#endif
