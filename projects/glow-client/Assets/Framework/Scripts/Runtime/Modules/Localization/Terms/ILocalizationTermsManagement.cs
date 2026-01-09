using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Modules.Localization.Terms
{
    public interface ILocalizationTermsManagement
    {
        UniTask Initialize(CancellationToken cancellationToken, ILocalizationAssetSource assetSource);
        UniTask Load(CancellationToken cancellationToken, string tableReference);
        void Unload();
        void Dump();
    }
}
