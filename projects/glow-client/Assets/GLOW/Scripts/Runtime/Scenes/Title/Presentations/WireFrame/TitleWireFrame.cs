using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Transitions;
using WonderPlanet.SceneManagement;
using Zenject;

namespace GLOW.Scenes.Title.Presentations.WireFrame
{
    public class TitleWireFrame
    {
        [Inject] ISceneNavigation SceneNavigation { get; }

        public void SwitchHomeScene()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
            SceneNavigation.Switch<HomeTopTransition>(default, SceneInBuildName.HOME).Forget();
        }
    }
}
