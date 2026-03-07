using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class OutGameAssetUnLoader : IOutGameAssetUnLoader
    {
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IGachaContentAssetLoader GachaContentAssetLoader { get; }
        [Inject] IHomeMainKomaPatternLoader HomeMainKomaPatternLoader { get; }

        void IOutGameAssetUnLoader.UnLoad()
        {
            UnitImageLoader.Unload();
            GachaContentAssetLoader.Unload();
            HomeMainKomaPatternLoader.Unload();
        }
    }
}
