using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Core.Modules.Advertising
{
    public interface IAdvertisingManager
    {
        public bool IsInitialized { get; }
        void Initialize();
        void LoadAndCacheRewardedAd(CancellationToken cancellationToken);
        void DestroyAdAll();
    }
}
