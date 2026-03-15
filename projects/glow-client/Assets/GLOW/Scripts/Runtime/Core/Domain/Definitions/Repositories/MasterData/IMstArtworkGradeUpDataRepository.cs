using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstArtworkGradeUpDataRepository
    {
        MstArtworkGradeUpModel GetArtworkGradeUp(
            Rarity rarity,
            ArtworkGradeLevel gradeLevel,
            MasterDataId mstSeriesId,
            MasterDataId mstArtworkId);
        IReadOnlyList<MstArtworkGradeUpModel> GetMstArtworkGradeUps(
            Rarity rarity,
            MasterDataId mstSeriesId,
            MasterDataId mstArtworkId);
    }
}
