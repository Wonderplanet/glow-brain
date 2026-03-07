using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    public interface IHomeMainKomaSettingViewControl
    {
        void SetKomaPatternName(HomeMainKomaPatternName komaName);

        void OnUnitEditButtonTapped(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId currentSettingMstUnitId,
            IReadOnlyList<MasterDataId> otherSettingMstUnitIds,
            Action<HomeMainKomaPatternViewModel> onUpdate);
    }

    public class HomeMainKomaSettingViewController : UIViewController<HomeMainKomaSettingView>,
        IHomeMainKomaSettingViewControl,
        IEscapeResponder
    {
        [Inject] IHomeMainKomaSettingViewDelegate ViewDelegate { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        HomeMainKomaSettingRotationItemController _rotationItemController;
        bool _isRotationItemControllerInitialized => _rotationItemController != null;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);

            ViewDelegate.OnViewDidLoad();
        }


        public void InitializePageView(HomeMainKomaSettingViewModel viewModel)
        {
            _rotationItemController ??= new HomeMainKomaSettingRotationItemController(
                () => ViewFactory.Create<HomeMainKomaSettingItemViewController>(),
                this);

            _rotationItemController.SetAutoScrollInterval(0f);// 自動スクロール無効化
            _rotationItemController.SetUpPages(viewModel.HomeMainKomaPatternViewModels, this, ActualView.PageView);
            _rotationItemController.MoveFromIndex(viewModel.InitialSelectedIndex);
        }
        void IHomeMainKomaSettingViewControl.SetKomaPatternName(HomeMainKomaPatternName komaName)
        {
            ActualView.SetKomaPatternName(komaName);
        }

        void IHomeMainKomaSettingViewControl.OnUnitEditButtonTapped(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId currentSettingMstUnitId,
            IReadOnlyList<MasterDataId> otherSettingMstUnitIds,
            Action<HomeMainKomaPatternViewModel> onUpdate)
        {
            ViewDelegate.OnUnitEditButtonTapped(
                targetMstHomeMainKomaPatternId,
                targetUnitAssetSetPlaceIndex,
                currentSettingMstUnitId,
                otherSettingMstUnitIds,
                onUpdate);
        }


        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            var currentMstKomaPatternId = _rotationItemController.CurrentMstKomaPatternId;
            ViewDelegate.OnEscape(currentMstKomaPatternId);
            return true;
        }

        void OnClose()
        {
            var currentMstKomaPatternId = _rotationItemController.CurrentMstKomaPatternId;
            ViewDelegate.OnClose(currentMstKomaPatternId);
        }

        [UIAction]
        void OnLeftButtonTapped()
        {
            if (!_isRotationItemControllerInitialized) return;
            _rotationItemController.MoveLeftButton();
        }

        [UIAction]
        void OnRightButtonTapped()
        {
            if (!_isRotationItemControllerInitialized) return;
            _rotationItemController.MoveRightButton();
        }

        [UIAction]
        void OnHelpButtonTapped()
        {
            ViewDelegate.OnHelpButtonTapped();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            OnClose();
        }

    }
}
