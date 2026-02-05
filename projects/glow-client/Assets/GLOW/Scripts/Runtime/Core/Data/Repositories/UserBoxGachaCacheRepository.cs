using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;

namespace GLOW.Core.Data.Repositories
{
    public class UserBoxGachaCacheRepository : IUserBoxGachaCacheRepository
    {
        IReadOnlyList<UserBoxGachaModel> _boxGachaCache = new List<UserBoxGachaModel>();
        UserBoxGachaModel IUserBoxGachaCacheRepository.GetFirstOrDefault(MasterDataId mstBoxGachaId)
        {
            return _boxGachaCache.FirstOrDefault(
                x => x.MstBoxGachaId == mstBoxGachaId, 
                UserBoxGachaModel.Empty);
        }

        void IUserBoxGachaCacheRepository.Save(UserBoxGachaModel userBoxGachaModel)
        {
            _boxGachaCache = _boxGachaCache.ReplaceOrAdd(
                x => x.MstBoxGachaId == userBoxGachaModel.MstBoxGachaId, 
                userBoxGachaModel);
        }
    }
}