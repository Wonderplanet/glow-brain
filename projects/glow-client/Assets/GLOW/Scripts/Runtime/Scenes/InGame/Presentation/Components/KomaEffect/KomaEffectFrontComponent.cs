using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaEffectFrontComponent : KomaEffectComponent
    {
        [Inject] IKomaEffectPrefabContainer KomaEffectPrefabContainer { get; }

        protected override KomaEffectView InstantiateKomaEffectView(KomaEffectType komaEffectType)
        {
            var assetKey = KomaEffectAssetKey.FromKomaEffectType(komaEffectType);

            bool isLoaded = KomaEffectPrefabContainer.IsFrontPrefabLoaded(assetKey);
            Hidden = !isLoaded;

            if (!isLoaded)
            {
                return null;
            }

            var frontPrefab = KomaEffectPrefabContainer.GetFrontPrefab(assetKey);
            var komaEffectGameObject = Instantiate(frontPrefab, transform);

            return komaEffectGameObject.GetComponent<KomaEffectView>();
        }
    }
}
