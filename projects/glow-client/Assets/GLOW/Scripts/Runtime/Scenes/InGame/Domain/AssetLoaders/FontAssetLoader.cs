using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using TMPro;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.AssetLoaders
{
    public interface IFontAssetLoader
    {
        UniTask Load(CancellationToken cancellationToken, FontAssetPath assetPath);
        void Unload();
    }

    public interface IFontAssetContainer
    {
        TMP_FontAsset Get(FontAssetPath assetPath);
    }

    public class FontAssetLoader : IFontAssetLoader, IFontAssetContainer
    {
        [Inject(Id = TemplateInjectId.AssetContainer.InGame)] IAssetReferenceContainerRepository AssetReferenceContainerRepository { get; }

        string ContainerKey => GlowAssetReferenceContainerId.Font;

        async UniTask IFontAssetLoader.Load(CancellationToken cancellationToken, FontAssetPath assetPath)
        {
            await AssetReferenceContainerRepository.Load<TMP_FontAsset>(
                cancellationToken,
                ContainerKey,
                assetPath.ToString());
        }

        void IFontAssetLoader.Unload()
        {
            var referenceContainer = AssetReferenceContainerRepository.Get<TMP_FontAsset>(ContainerKey);
            referenceContainer?.Unload();
        }

        TMP_FontAsset IFontAssetContainer.Get(FontAssetPath assetPath)
        {
            return AssetReferenceContainerRepository.Get<TMP_FontAsset>(ContainerKey).Get(assetPath.ToString());
        }
    }
}
