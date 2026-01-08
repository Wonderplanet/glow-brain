using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOprGachaRepository
    {
        IReadOnlyList<OprGachaModel> GetOprGachaModelsByDataTime(DateTimeOffset dateTime);
        OprGachaModel GetOprGachaModelFirstOrDefaultById(MasterDataId gachaId);
        IReadOnlyList<OprGachaDisplayUnitI18nModel> GetOprGachaDisplayUnitI18nModelsById(MasterDataId gachaId);
        IReadOnlyList<OprGachaDisplayUnitI18nModel> GetOprGachaDisplayUnitI18nModels();
    }
}
