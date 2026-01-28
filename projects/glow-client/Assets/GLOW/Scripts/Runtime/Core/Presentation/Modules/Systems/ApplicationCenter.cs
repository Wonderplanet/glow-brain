using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.SceneTransition;
using GLOW.Core.Domain.Constants;
using UnityEngine;
using WonderPlanet.SceneManagement;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Presentation.Modules.Systems
{
    public sealed class ApplicationCenter : IApplicationRebootor, IApplicationTerminator
    {
        [Inject] ISceneNavigation SceneNavigation { get; }

        void IApplicationRebootor.SoftReboot()
        {
            // NOTE: Titleシーンへ遷移するだけ
            SceneNavigation.Switch<MaskTransition>(default, SceneInBuildName.TITLE, "WhiteWipeTransition", true).Forget();
        }

        void IApplicationRebootor.Reboot()
        {
            // NOTE: Applicationシーン自体も破棄して再生成する
            //       ApplicationDelegateで呼ばれるTransitionと合わせておくと違和感がない
            SceneNavigation.Switch<MaskTransition>(
                default,
                SceneInBuildName.REBOOT,
                SceneTransitionVariant.ApplicationTransition,
                true).Forget();
        }

        void IApplicationTerminator.Terminate()
        {
            Application.Quit(0);
        }
    }
}
