using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Domain.Modules
{
    public interface ISoundEffectManagement
    {
        UniTask Load(CancellationToken cancellationToken, string[] assetKeys);
        void Unload(string assetKey);
        void Unload(string[] assetKeys);
        void Unload();
    }
}
