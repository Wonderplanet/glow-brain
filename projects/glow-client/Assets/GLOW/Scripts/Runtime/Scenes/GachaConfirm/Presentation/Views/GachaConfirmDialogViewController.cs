using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaConfirm.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-19_ガシャ確認ダイアログ
    /// </summary>
    public class GachaConfirmDialogViewController : UIViewController<GachaConfirmDialogView>, IEscapeResponder
    {
        [Inject] IGachaConfirmDialogViewDelegate _delegate;
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyViewDelegate { get; }

        public record Argument(MasterDataId GachaId, GachaDrawType GachaDrawType);

        GachaDrawCount _drawCount;
        CostAmount _costAmount;
        CostType _costType;
        MasterDataId _costId;
        MasterDataId _gachaId;
        GachaType _gachaType;
        bool _isReDraw;

        public bool IsReDraw
        {
            set => _isReDraw = value;
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            _delegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void SetViewModel(GachaConfirmDialogViewModel viewModel)
        {
            // チケットで引く場合、不足時は表示されない
            if (viewModel.CostType == CostType.Item && viewModel.DrawableFlag.Value == false)
            {
                // 通らない
                // 表示後に非表示にして元画面を更新する

            }

            _drawCount = viewModel.GachaDrawCount;
            _costAmount = viewModel.CostAmount;
            _costType = viewModel.CostType;
            _gachaId = viewModel.GachaId;
            _gachaType = viewModel.GachaType;
            _costId = viewModel.CostId;

            ActualView.SetViewModel(viewModel);
        }

        public void CloseDialog()
        {
            Dismiss();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (TutorialBackKeyViewDelegate.IsPlayingTutorial())
            {
                // トーストでバックキーが無効であると表示する
                CommonToastWireFrame.ShowInvalidOperationMessage();

                return true;
            }

            return false;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            CloseDialog();
        }

        [UIAction]
        void OnTransitShopButtonTapped()
        {
            _delegate.TransitionToShopView();
        }

        [UIAction]
        void OnGachaDrawButtonTapped()
        {
            // チュートリアルガシャの場合
            if (_gachaType == GachaType.Tutorial)
            {
                Debug.Log("TutorialGachaDraw");
                _delegate.TutorialGachaDraw();
                return;
            }

            var drawCount =  _drawCount;
            var costAmount =  _costAmount;

            // アイテムコストの単発ガシャは数量選択を取得する
            if (_costType == CostType.Item && _drawCount.IsSingleDraw())
            {
                drawCount = new GachaDrawCount(ActualView.GachaDrawCount.Value);
                costAmount = new CostAmount(drawCount.Value * _costAmount.Value);
            }

            _delegate.GachaDraw(
                _gachaId,
                _gachaType,
                drawCount,
                _costType,
                costAmount,
                _costId,
                _isReDraw);
        }

        [UIAction]
        void OnSpecificCommerceButtonTapped()
        {
            // 特定商取引法の表示
            _delegate.OnSpecificCommerceButtonTapped();
        }
    }
}
