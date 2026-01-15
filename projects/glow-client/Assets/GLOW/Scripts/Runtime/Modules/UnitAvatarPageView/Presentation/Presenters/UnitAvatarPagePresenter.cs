using GLOW.Modules.UnitAvatarPageView.Domain.Interfaces;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using Zenject;

namespace GLOW.Modules.UnitAvatarPageView.Presentation.Presenters
{
    public class UnitAvatarPagePresenter : IUnitAvatarPageViewDelegate
    {
        [Inject] IUnitAvatarPageViewController ViewController { get; }
        [Inject] IUnitAvatarPageViewController.Argument Argument { get; }
        [Inject] IGetSpineAvatarImageUseCase GetUnitAvatarImageUseCase { get; }

        public void OnViewWillAppear()
        {
            var model = GetUnitAvatarImageUseCase.GetImagePath(Argument.MstUnitId);
            ViewController.SetupAvatar(model.ImagePath, model.Color, model.IsPhantomized);
        }
    }
}
