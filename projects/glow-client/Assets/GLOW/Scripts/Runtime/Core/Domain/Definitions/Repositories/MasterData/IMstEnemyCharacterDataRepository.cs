using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstEnemyCharacterDataRepository
    {
        IReadOnlyList<MstEnemyCharacterModel> GetEnemyCharacters();
        IReadOnlyList<MstEnemyCharacterModel> GetSeriesEnemyCharacters(MasterDataId mstSeriesId);
        MstEnemyCharacterModel GetEnemyCharacter(MasterDataId mstEnemyCharacterId);
        MstEnemyStageParameterModel GetEnemyStageParameter(MasterDataId id);
    }
}
