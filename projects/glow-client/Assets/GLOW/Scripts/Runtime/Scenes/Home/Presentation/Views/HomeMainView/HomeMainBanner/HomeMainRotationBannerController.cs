using System;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.Home.Presentation.Views.HomeMainBanner;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public class HomeMainRotationBannerController : RotationPageController<HomeMainBannerItemViewController, HomeMainBannerItemViewModel>
    {
        Func<HomeMainBannerItemViewController> _createItemViewAction;

        public HomeMainRotationBannerController(Func<HomeMainBannerItemViewController> createItemViewAction)
        {
            _createItemViewAction = createItemViewAction;
        }

        protected override HomeMainBannerItemViewController[] NewItemViewControllersArray(int count)
        {
            return new HomeMainBannerItemViewController[count];
        }

        protected override void OnSwipeAnimationFinished()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            
        }

        protected override void OnNonSwipeAnimationFinished()
        {
        }

        protected override void PlayIndicatorClickSound()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        protected override void SetItemViewControllerIfNeed(int index)
        {
            if (_itemViewControllers[index] == null)
            {
                _itemViewControllers[index] = _createItemViewAction.Invoke();
                _itemViewControllers[index].SetViewModel(_itemViewModels[index]);
            }
        }
    }
}
