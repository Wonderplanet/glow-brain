using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Localization
{
    public interface ILocalizationAssetManagement
    {
        UniTask Initialize(CancellationToken cancellationToken);
        UniTask PreloadAssetDatabase(CancellationToken cancellationToken, string tableReference);
        UniTask PreloadStringDatabase(CancellationToken cancellationToken, string tableReference);
        void ReleaseStringTable(string tableReference);
        void ReleaseAssetTable(string tableReference);
    }
}
