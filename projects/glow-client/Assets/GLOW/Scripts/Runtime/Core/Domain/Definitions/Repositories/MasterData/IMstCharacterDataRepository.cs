using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstCharacterDataRepository
    {
        IReadOnlyList<MstCharacterModel> GetCharacters();
        IReadOnlyList<MstCharacterModel> GetSeriesCharacters(MasterDataId mstSeriesId);
        MstCharacterModel GetCharacter(MasterDataId id);
        MstCharacterModel GetCharacterByFragmentMstItemId(MasterDataId id);
#if GLOW_DEBUG
        void RefreshCharacterModelCache(List<MstCharacterModel> mstDebugCharacterModels);
#endif
    }
}
