using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IUserBoxGachaCacheRepository
    {
        UserBoxGachaModel GetFirstOrDefault(MasterDataId mstBoxGachaId);
        void Save(UserBoxGachaModel userBoxGachaModel);
    }
}