using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Domain.Modules
{
    public interface IBackgroundMusicManagement
    {
        UniTask Load(CancellationToken cancellationToken, string assetKey);
        void Unload(string assetKey);
        void Unload();
    }
}
