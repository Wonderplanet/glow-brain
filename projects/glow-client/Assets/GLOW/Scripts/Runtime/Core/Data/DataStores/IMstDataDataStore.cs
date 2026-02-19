using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Data.DataStores
{
    public interface IMstDataDataStore
    {
        UniTask Load(
            CancellationToken cancellationToken,
            MasterType masterType,
            string name,
            string hash,
            Language language);

        void Save(string name, MasterType masterType, byte[] data);
        bool Validate(MasterType masterType, string name);
        IEnumerable<T> Get<T>() where T : class;
        void DeleteAll(MasterType masterType);
    }
}
