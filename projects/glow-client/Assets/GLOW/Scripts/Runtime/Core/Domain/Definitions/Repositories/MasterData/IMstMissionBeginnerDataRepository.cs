using System.Collections.Generic;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstMissionBeginnerDataRepository
    {
        public IReadOnlyList<MstMissionBeginnerModel> GetMstMissionBeginnerModels();
        public IReadOnlyList<MstMissionBeginnerPromptPhraseModel> GetMstMissionBeginnerPromptPhraseModels();
    }
}