using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Repositories
{
    public class GashaPrizeCacheRepository : IGashaPrizeCacheRepository
    {
        Dictionary<MasterDataId, GachaPrizeResultModel> _resultModels = new Dictionary<MasterDataId, GachaPrizeResultModel>();

        public void Set(MasterDataId masterDataId, GachaPrizeResultModel model)
        {
            if (!_resultModels.ContainsKey(masterDataId)) _resultModels.Add(masterDataId, null);
            _resultModels[masterDataId] = model;
        }

        public GachaPrizeResultModel Get(MasterDataId masterDataId)
        {
            return _resultModels[masterDataId];
        }

        public bool TryGetValue(MasterDataId masterDataId, out GachaPrizeResultModel value)
        {
            return _resultModels.TryGetValue(masterDataId, out value);
        }

        public void Clear()
        {
            _resultModels.Clear();
        }
    }
}
