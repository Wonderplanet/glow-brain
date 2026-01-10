using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-1_ガシャトップ
    /// </summary>
    public class GachaContentViewController : HomeBaseViewController<GachaContentView>, IGachaUnitAvatarPageListDelegate
    {
        [Inject] IGachaContentViewDelegate _delegate;
        [Inject] IViewFactory ViewFactory { get; }

        List<GachaDisplayUnitViewModel> _unitInfo;
        MasterDataId _gachaId;
        GachaDrawFromContentViewFlag _gachaDrawFromContentViewFlag = GachaDrawFromContentViewFlag.False;
        bool _isEnableScroll = true;

        public record Argument(MasterDataId GachaId);

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            _delegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            if (_gachaDrawFromContentViewFlag)
            {
                // ガシャを引いた後の更新を行う
                _delegate.UpdateView();
                _gachaDrawFromContentViewFlag = GachaDrawFromContentViewFlag.False;
            }
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            _delegate.OnViewDidUnLoad();
        }

        public void UpdateView()
        {
            _delegate.UpdateView();
        }

        public void SetViewModel(GachaContentViewModel viewModel)
        {
            _gachaId = viewModel.GachaId;
            ActualView.SetViewModel(viewModel);

            if(viewModel.GachaDisplayUnitViewModels.Count == 0)
            {
                ActualView.AvatarPageList.gameObject.SetActive(false);
                ActualView.HideUnitInfo();
                return;
            }

            _unitInfo = viewModel.GachaDisplayUnitViewModels;
            // キャラが2体以上の場合表情３再生後に次のキャラを表示する
            Action action = viewModel.GachaDisplayUnitViewModels.Count > 1
                ? () => UnitCutInAnimationEndAction(scrollFinishSeSuppression:false)
                : null;
            ActualView.SetUnitInfo(viewModel.GachaDisplayUnitViewModels[0], action);

            ActualView.AvatarPageList.Delegate = this;
            ActualView.AvatarPageList.Setup(
                ViewFactory,
                this,
                viewModel.GachaDisplayUnitViewModels.Select(x => x.UnitId).ToList(),
                viewModel.GachaDisplayUnitViewModels[0].UnitId);
        }

        public void UpdateViewModel(GachaContentViewModel viewModel)
        {
            ActualView.SetViewModel(viewModel);
        }

        public void DisableScroll()
        {
            _isEnableScroll = false;
        }
        public void EnableScroll()
        {
            _isEnableScroll = true;
            var currentUnitId = ActualView.AvatarPageList.GetUnitId();
            var model = _unitInfo.Find(x => x.UnitId == currentUnitId);
            ActualView.ChangeUnitInfo(model);
        }

        void IGachaUnitAvatarPageListDelegate.SwitchUnit(MasterDataId unitId)
        {
            var model = _unitInfo.Find(x => x.UnitId == unitId);
            ActualView.ChangeUnitInfo(model);
        }

        void IGachaUnitAvatarPageListDelegate.WillTransitionTo()
        {
            ActualView.Interactable = false;
        }

        void IGachaUnitAvatarPageListDelegate.DidFinishAnimating(bool finished, bool transitionCompleted)
        {
            ActualView.Interactable = finished;
        }

        void IGachaUnitAvatarPageListDelegate.DidCancelTransition()
        {
            if (ActualView.IsUnitCutInAnimationPlaying()) return;

            ActualView.ReplayUnitCutInAnimation();
        }

        void ShowGachaConfirmDialog(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            // ガチャ確認ダイアログの表示
            _gachaDrawFromContentViewFlag = GachaDrawFromContentViewFlag.True;
            _delegate.ShowGachaConfirmDialogView(gachaId, gachaDrawType, _gachaDrawFromContentViewFlag);
        }

        void UnitCutInAnimationEndAction(bool scrollFinishSeSuppression)
        {
            if(!_isEnableScroll) return;

            // アニメーション再生中は再度同じキャラでカットイン再生
            if (ActualView.IsTransitioning)
            {
                ActualView.ReplayUnitCutInAnimation();
                return;
            }

            ActualView.AvatarPageList.ScrollToNextPage(scrollFinishSeSuppression);
        }

        void ScrollToNextPage(bool scrollFinishSeSuppression)
        {
            if(!_isEnableScroll) return;
            ActualView.AvatarPageList.ScrollToNextPage(scrollFinishSeSuppression);
        }

        [UIAction]
        public void OnBackButtonTapped()
        {
            _delegate.OnBackButtonTapped();
        }

        [UIAction]
        public void OnDrawAdGachaButtonTapped()
        {
            ShowGachaConfirmDialog(_gachaId, GachaDrawType.Ad);
        }

        [UIAction]
        public void OnSingleDrawButtonTapped()
        {
            ShowGachaConfirmDialog(_gachaId, GachaDrawType.Single);
        }

        [UIAction]
        public void OnMultiDrawButtonTapped()
        {
            ShowGachaConfirmDialog(_gachaId, GachaDrawType.Multi);
        }

        [UIAction]
        public void OnUnitDetailButtonTapped()
        {
            // ユニット詳細ボタンタップ時
            var unitId = ActualView.AvatarPageList.GetUnitId();
            _delegate.OnUnitDetailViewButton(unitId);

            // ユニットアニメーション中の場合はリセット
            ActualView.AvatarPageList.ResetUnitAnimation();
        }

        [UIAction]
        public void OnGachaDetailButtonTapped()
        {
            // ガチャ詳細ボタンタップ時
            _delegate.OnGachaDetailButtonTapped(_gachaId);
        }

        [UIAction]
        public void OnGachaProvisionRatioButtonTapped()
        {
            //　提供割合ボタンタップ時
            _delegate.OnGachaProvisionRatioTapped(_gachaId);

        }

        [UIAction]
        public void OnSpecificCommerceButtonTapped()
        {
            // 特商法ボタンタップ時
            _delegate.OnSpecificCommerceButtonTapped();
        }

        [UIAction]
        void OnRightArrowButtonTapped()
        {
            ScrollToNextPage(scrollFinishSeSuppression:true);
        }

        [UIAction]
        void OnLeftArrowButtonTapped()
        {
            if(!_isEnableScroll) return;

            ActualView.AvatarPageList.ScrollToPrevPage(scrollFinishSeSuppression:true);
        }

        [UIAction]
        void OnSpecialAttackButtonTapped()
        {
            var unitId = ActualView.AvatarPageList.GetUnitId();
            _delegate.OnSpecialAttackButtonTapped(unitId);
        }
    }
}
