using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.HomeHelpDialog.Domain.ScriptableObjects;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;

namespace GLOW.Scenes.HomeHelpDialog.Domain.AssetLoaders
{
    public interface IHomeHelpInfoListAssetLoader
    {
        UniTask<HomeHelpInfoList> Load(CancellationToken cancellationToken, HomeHelpInfoAssetKey assetKey);
        void Unload(HomeHelpInfoAssetKey assetKey);
    }
}
