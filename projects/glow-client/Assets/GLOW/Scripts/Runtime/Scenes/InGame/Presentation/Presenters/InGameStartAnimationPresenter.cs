using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Presentation.Views;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public class InGameStartAnimationPresenter : IInGameStartAnimationViewDelegate
    {
        [Inject] InGameStartAnimationViewController ViewController { get; }
        [Inject] InGameStartAnimationViewController.Argument Argument { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(InGameStartAnimationPresenter), nameof(OnViewDidLoad));
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_001);
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(InGameStartAnimationPresenter), nameof(OnViewDidUnload));
        }

        public void OnAnimationCompleted()
        {
            ViewController.Dismiss(animated:false, completion:Argument.OnViewClosed);
        }
    }
}
