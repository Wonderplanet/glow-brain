using System.Threading;
using Cysharp.Threading.Tasks;
using UnityHTTPLibrary;
using WPFramework.Constants.Zenject;
using WPFramework.Data.Extensions;
using WPFramework.Domain.Services;
using Zenject;

namespace WPFramework.Data.Services
{
    public sealed class MstDataService : IMstDataService
    {
        [Inject(Id = FrameworkInjectId.ServerApi.Mst)] ServerApi MstContext { get; }

        public async UniTask<byte[]> FetchMstData(CancellationToken cancellationToken, string path)
        {
            var payload = new Payload();
            return await MstContext.Get<byte[]>(cancellationToken, path, payload);
        }
    }
}
