using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using GLOW.Scenes.GachaRatio.Presentation.Translator;
using GLOW.Scenes.GachaWireFrame.Presentation.Presenters;
using GLOW.Scenes.PackShopGacha.Domain.UseCases;
using GLOW.Scenes.PackShopGacha.Presentation.Translators;
using GLOW.Scenes.PackShopGacha.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.PackShopGacha.Presentation.Presenters
{
    public class PackShopGachaPresenter : IPackShopGachaViewDelegate
    {
        [Inject] PackShopGachaViewController.Argument Argument { get; }
        [Inject] PackShopGachaUseCase PackShopGachaUseCase { get; }
        [Inject] PackShopGachaViewController ViewController { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] GetCommonReceiveItemUseCase GetCommonReceiveItemUseCase { get; }
        [Inject] IGachaTransitionFromShopControl GachaWireFrame { get; }
        [Inject] GachaRatioDialogUseCase GachaRatioDialogUseCase { get; }

        bool _isBannerTapped;

        public void OnViewDidLoad()
        {
            // 外から渡されたチケットのIdから
            var useCaseModel = PackShopGachaUseCase.GetPackShopGachaUseCaseModel(Argument.TicketId);

            var viewModel = PackShopGachaViewModelTranslator.Translate(useCaseModel);

            ViewController.SetUp(viewModel);
        }

        void IPackShopGachaViewDelegate.OnBannerTapped(MasterDataId gachaId)
        {
            if(_isBannerTapped) return;

            _isBannerTapped = true;

            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaRatioDialogUseCase.GetGachaRatioUseCaseModel(ct, gachaId);
                var viewModel = GachaRatioDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);
                var targetPosY = ViewController.GetNormalizedPos();

                // お知らせ情報がなければガシャ提供割合を開く
                GachaWireFrame.OnGachaRatioCloseAction = () => ShowPackShopGacha(targetPosY);
                GachaWireFrame.ShowGachaRatioDialogView(gachaId, viewModel, ViewController);

                _isBannerTapped = false;
                ViewController.Dismiss();
            });
        }

        void IPackShopGachaViewDelegate.OnClose()
        {
            Argument.PackShopViewController.ShowProductInfo(Argument.MstPackId);
            ViewController.Dismiss();
        }

        void OnClickIconDetail(GachaRatioResourceModel resourceModel)
        {
            var playerResourceModel = GetCommonReceiveItemUseCase.GetPlayerResource(
                resourceModel.ResourceType,
                resourceModel.MasterDataId,
                resourceModel.Amount
            );

            GachaWireFrame.OnClickIconDetail(playerResourceModel, Argument.PackShopViewController.UIViewController);
        }

        void ShowPackShopGacha(float targetPosY)
        {
            Argument.PackShopViewController.ShowPackShopGacha(
                Argument.TicketId,
                Argument.MstPackId,
                targetPosY);
        }
    }
}
