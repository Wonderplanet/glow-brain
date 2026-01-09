using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine.AddressableAssets;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IUnitAttackViewInfoSetLoader
    {
        UniTask Load(UnitAssetKey assetKey, CancellationToken cancellationToken);
        void Unload();
    }

    public interface IUnitAttackViewInfoSetContainer
    {
        UnitAttackViewInfoSet GetUnitAttackViewInfo(UnitAssetKey assetKey);
    }

    public class UnitAttackViewInfoSetLoader : IUnitAttackViewInfoSetLoader, IUnitAttackViewInfoSetContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.UnitAttackViewInfoSet;

        async UniTask IUnitAttackViewInfoSetLoader.Load(UnitAssetKey assetKey, CancellationToken cancellationToken)
        {
            var assetPath = UnitAttackViewInfoSetAssetPath.FromAssetKey(assetKey);

            try
            {
                await AssetReferenceContainerRepository.Load<UnitAttackViewInfoSet>(
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
                ApplicationLog.LogError(nameof(UnitAttackViewInfoSetLoader), e.ToString());
            }
        }
        
        void IUnitAttackViewInfoSetLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<UnitAttackViewInfoSet>(ContainerKey);
            referenceContainer?.Unload();
        }

        UnitAttackViewInfoSet IUnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(UnitAssetKey assetKey)
        {
            var assetPath = UnitAttackViewInfoSetAssetPath.FromAssetKey(assetKey);
            return AssetReferenceContainerRepository.Get<UnitAttackViewInfoSet>(ContainerKey)?.Get(assetPath.Value);
        }
    }
}