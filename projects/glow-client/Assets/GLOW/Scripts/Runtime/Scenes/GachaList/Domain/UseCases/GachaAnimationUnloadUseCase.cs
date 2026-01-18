using GLOW.Core.Domain.Constants;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public class GachaAnimationUnloadUseCase
    {
        [Inject] IBackgroundMusicManagement BackgroundMusicManagement { get; }

        public void UnloadGachaAnimAsset()
        {
            BackgroundMusicManagement.Unload(BGMAssetKeyDefinitions.BGM_gacha_animation);
        }
    }
}
