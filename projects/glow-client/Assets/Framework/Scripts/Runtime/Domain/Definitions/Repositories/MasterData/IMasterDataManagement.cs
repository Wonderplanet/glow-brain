using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Constants.MasterData;

namespace WPFramework.Domain.Repositories
{
    public interface IMasterDataManagement
    {
        UniTask Load(CancellationToken cancellationToken, MasterType masterType, string masterPath, string hash);
        void Save(MasterType masterType, string name, byte[] data);
        UniTask<bool> Validate(CancellationToken cancellationToken, MasterType masterType, string name, string hash);
        void DeleteAll(MasterType masterType);
    }
}
