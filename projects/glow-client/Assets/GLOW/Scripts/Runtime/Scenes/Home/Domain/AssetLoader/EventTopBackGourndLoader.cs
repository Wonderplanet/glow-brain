using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;
using UnityEngine.AddressableAssets;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Home.Domain.AssetLoader
{
    public interface IBackGroundSpriteLoader
    {
        UniTask Load(CancellationToken cancellationToken, KomaBackgroundAssetPath assetPath);
        void Unload();
    }

    public interface IBackGroundSpriteContainer
    {
        Sprite Get(KomaBackgroundAssetPath path);
    }

    public class EventTopBackGroundLoader:
        IBackGroundSpriteLoader,
        IBackGroundSpriteContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.OutGame)]
        IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }
        string ContainerKey => GlowAssetReferenceContainerId.EventTopBackground;

        async UniTask IBackGroundSpriteLoader.Load(CancellationToken cancellationToken, KomaBackgroundAssetPath assetPath)
        {
            try
            {
                await AssetReferenceContainerRepository.Load<Sprite>(
                    cancellationToken,
                    ContainerKey,
                    assetPath.Value);
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
                ApplicationLog.LogError(nameof(EventTopBackGroundLoader), e.ToString());
            }
        }

        void IBackGroundSpriteLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<Sprite>(ContainerKey);
            referenceContainer?.Unload();
        }

        Sprite IBackGroundSpriteContainer.Get(KomaBackgroundAssetPath path)
        {
            return AssetReferenceContainerRepository.Get<Sprite>(ContainerKey)?.Get(path.Value);
        }
    }
}
