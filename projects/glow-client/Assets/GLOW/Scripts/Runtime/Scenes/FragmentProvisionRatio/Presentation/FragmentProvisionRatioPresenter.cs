using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.FragmentProvisionRatio.Domain;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.ViewModels;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation
{
    /// <summary>
    /// 81_アイテムBOXリスト
    /// 　81-3_アイテムBOXページダイアログ
    /// 　　81-3-5_ランダムかけらBOX提供割合ダイアログ
    /// </summary>
    public sealed class FragmentProvisionRatioPresenter : IFragmentProvisionRatioViewDelegate
    {
        [Inject] FragmentProvisionRatioUseCase UseCase { get; }
        [Inject] FragmentProvisionRatioViewController ViewController { get; }
        [Inject] FragmentProvisionRatioViewController.Argument Args{ get;}
        [Inject] RandomFragmentBoxWireFrame WireFrame { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] CheckTransitToShopUseCase CheckTransitToShopUseCase { get; }
        [Inject] ItemDetailTransitionWireFrame ItemDetailTransitionWireFrame { get; }

        void IFragmentProvisionRatioViewDelegate.OnViewDidLoad()
        {
            var model = UseCase.GetUseCaseModel(Args.MstFragmentBoxGroupId, Args.RandomFragmentBoxMstItemId);
            var viewModel = FragmentProvisionRatioViewModelTranslator.Translate(model);
            ViewController.SetViewModel(viewModel);
        }

        void IFragmentProvisionRatioViewDelegate.OnShowUnitView(MasterDataId mstUnitId)
        {
            var argument = UnitDetailViewController.Argument.CreateMaxStatus(mstUnitId);
            WireFrame.OnShowUnitView(argument);
        }

        void IFragmentProvisionRatioViewDelegate.OnTransitShop()
        {
            if (CheckTransitToShopUseCase.ShouldTransitShopView(Args.RandomFragmentBoxMstItemId)) WireFrame.OnTransitShop();
            else ShowCantTransitModal();
        }

        void IFragmentProvisionRatioViewDelegate.OnClose()
        {
            WireFrame.OnCloseProvisionRatio();
        }

        public void OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel)
        {
            ItemDetailTransitionWireFrame.Transit(earnLocationViewModel);
        }

        void ShowCantTransitModal()
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "こちらの商品は現在販売を終了しております。");
        }
    }
}
