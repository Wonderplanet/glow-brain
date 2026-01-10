using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IGachaAnimationUnitInfoLoader
    {
        UniTask Load(CancellationToken cancellationToken, GachaAnimationUnitInfoAssetPath assetPath);
    }

    public interface IGachaAnimationUnitInfoContainer
    {
        GachaAnimationUnitInfo GetGachaAnimationUnitInfo(GachaAnimationUnitInfoAssetPath assetKey);
    }

    public class GachaAnimationUnitInfoLoader : IGachaAnimationUnitInfoLoader, IGachaAnimationUnitInfoContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.GachaAnimationUnitInfo;

        async UniTask IGachaAnimationUnitInfoLoader.Load(CancellationToken cancellationToken, GachaAnimationUnitInfoAssetPath assetPath)
        {
            await AssetReferenceContainerRepository.Load<GachaAnimationUnitInfo>(
                cancellationToken,
                ContainerKey,
                assetPath.Value);
        }


        GachaAnimationUnitInfo IGachaAnimationUnitInfoContainer.GetGachaAnimationUnitInfo(GachaAnimationUnitInfoAssetPath assetPath)
        {
            return AssetReferenceContainerRepository.Get<GachaAnimationUnitInfo>(ContainerKey).Get(assetPath.Value);
        }
    }
}
