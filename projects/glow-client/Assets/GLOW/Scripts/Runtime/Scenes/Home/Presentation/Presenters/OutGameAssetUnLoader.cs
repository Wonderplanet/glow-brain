using GLOW.Scenes.InGame.Domain.AssetLoaders;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class OutGameAssetUnLoader : IOutGameAssetUnLoader
    {
        [Inject] IUnitImageLoader UnitImageLoader { get; }

        void IOutGameAssetUnLoader.UnLoad()
        {
            UnitImageLoader.Unload();
        }
    }
}