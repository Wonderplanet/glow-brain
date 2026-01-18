#if GLOW_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Debugs.Home.Domain.Constants;

namespace GLOW.Debugs.Home.Domain.UseCases
{
    public class DebugGetMstUnitTemporaryParameterUseCase
    {
        public IReadOnlyList<MstCharacterModel> GetDebugDummyTemplates()
        {
            return DebugMstUnitTemporaryParameterDefinitions.DebugMstCharacterDummyTemplates;
        }
    }
}
#endif
