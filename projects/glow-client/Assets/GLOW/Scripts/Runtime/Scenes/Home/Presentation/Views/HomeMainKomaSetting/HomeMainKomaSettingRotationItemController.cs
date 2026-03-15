using System;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.PageContent;
using GLOW.Core.Presentation.Views.RotationBanner;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    public class HomeMainKomaSettingRotationItemController :
        RotationPageController<HomeMainKomaSettingItemViewController, HomeMainKomaPatternViewModel>
    {
        Func<HomeMainKomaSettingItemViewController> _createItemViewAction;
        IHomeMainKomaSettingViewControl _viewControl;

        public MasterDataId CurrentMstKomaPatternId => CurrentViewController.MstHomeMainKomaPatternId;
        public HomeMainKomaSettingRotationItemController(
            Func<HomeMainKomaSettingItemViewController> createItemViewAction,
            IHomeMainKomaSettingViewControl viewControl)
        {
            _createItemViewAction = createItemViewAction;
            _viewControl = viewControl;
        }

        protected override HomeMainKomaSettingItemViewController[] NewItemViewControllersArray(int count)
        {
            return new HomeMainKomaSettingItemViewController[count];
        }

        protected override void SetItemViewControllerIfNeed(int index)
        {
            if (_itemViewControllers[index] == null)
            {
                var model = _itemViewModels[index];
                _itemViewControllers[index] = _createItemViewAction.Invoke();
                _itemViewControllers[index].InitializeView(model, _viewControl);
                _itemViewControllers[index].SetUpView(model);
            }
        }

        protected override void PlayIndicatorClickSound()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        protected override void OnSwipeAnimationFinished()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        protected override void OnNonSwipeAnimationFinished()
        {
            //no use.
        }

        public void MoveRightButton()
        {
            MoveRight();
        }

        public void MoveLeftButton()
        {
            MoveLeft();
        }

        public void MoveFromIndex(HomeMainKomaSettingIndex index)
        {
            var vc = GetViewController(index.Value);
            // ページ変更
            MoveToViewController(
                vc,
                UIPageViewController.NavigationDirection.Forward,
                false
                );
        }
    }
}
