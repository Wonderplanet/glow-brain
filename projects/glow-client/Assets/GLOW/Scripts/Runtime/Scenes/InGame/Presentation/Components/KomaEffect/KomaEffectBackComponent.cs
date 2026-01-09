using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaEffectBackComponent : KomaEffectComponent
    {
        [Inject] IKomaEffectPrefabContainer KomaEffectPrefabContainer { get; }

        protected override KomaEffectView InstantiateKomaEffectView(KomaEffectType komaEffectType)
        {
            var assetKey = KomaEffectAssetKey.FromKomaEffectType(komaEffectType);

            bool isLoaded = KomaEffectPrefabContainer.IsBackPrefabLoaded(assetKey);
            Hidden = !isLoaded;

            if (!isLoaded)
            {
                return null;
            }

            var backPrefab = KomaEffectPrefabContainer.GetBackPrefab(assetKey);
            var komaEffectGameObject = Instantiate(backPrefab, transform);

            return komaEffectGameObject.GetComponent<KomaEffectView>();
        }
    }
}
