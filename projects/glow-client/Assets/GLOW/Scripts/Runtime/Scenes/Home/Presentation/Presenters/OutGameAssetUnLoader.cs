using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class OutGameAssetUnLoader : IOutGameAssetUnLoader
    {
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IGachaContentAssetLoader GachaContentAssetLoader { get; }

        void IOutGameAssetUnLoader.UnLoad()
        {
            UnitImageLoader.Unload();
            GachaContentAssetLoader.Unload();
        }
    }
}
