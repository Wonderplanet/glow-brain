using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using UnityEngine;
using UnityEngine.AddressableAssets;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Home.Domain.AssetLoader
{
    public class OutGameUnitImageLoader :
        IUnitImageLoader,
        IUnitImageContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.OutGame)]
        IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.UnitImage;

        async UniTask IUnitImageLoader.Load(CancellationToken cancellationToken, UnitImageAssetPath imageAssetPath)
        {
            try
            {
                await AssetReferenceContainerRepository.Load<GameObject>(
                    cancellationToken,
                    ContainerKey,
                    imageAssetPath.Value);
            }
            catch (OperationCanceledException)
            {
                throw;
            }
            catch (InvalidKeyException)
            {
                // この時点でエラーログが出てるので、ここでは何もしない
            }
            catch (Exception e)
            {
                ApplicationLog.LogError(nameof(OutGameUnitImageLoader), e.ToString());
            }
        }

        void IUnitImageLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<GameObject>(ContainerKey);
            referenceContainer?.Unload();
        }

        GameObject IUnitImageContainer.Get(UnitImageAssetPath imageAssetPath)
        {
            return AssetReferenceContainerRepository.Get<GameObject>(ContainerKey)?.Get(imageAssetPath.Value);
        }
    }
}
