using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.GachaUnitAvatarPage.Presentation.Views
{
    public class GachaUnitAvatarPageViewController : UnitAvatarPageViewController
    {

        [Inject] IGachaUnitAvatarPageViewDelegate Delegate { get; }
        public override void LoadView()
        {
            PrefabName = "GachaUnitAvatarPageView";
            base.LoadView();
        }
    }
}
