using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.User;

namespace GLOW.Core.Data.DataStores
{
    public interface IUserPropertyDataStore
    {
        UniTask Load(CancellationToken cancellationToken);
        void Save(UserPropertyData userPropertyData);
        void Delete();
        UserPropertyData Get();
    }
}
