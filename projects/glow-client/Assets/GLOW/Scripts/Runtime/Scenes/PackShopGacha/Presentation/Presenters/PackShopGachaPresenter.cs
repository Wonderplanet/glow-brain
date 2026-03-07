using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Scenes.GachaLineupDialog.Domain.UseCases;
using GLOW.Scenes.GachaLineupDialog.Presentation.Translator;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using GLOW.Scenes.GachaRatio.Presentation.Translator;
using GLOW.Scenes.GachaWireFrame.Presentation.Presenters;
using GLOW.Scenes.PackShopGacha.Domain.UseCases;
using GLOW.Scenes.PackShopGacha.Presentation.Translators;
using GLOW.Scenes.PackShopGacha.Presentation.Views;
using GLOW.Scenes.StepupGachaRatioDialog.Domain.UseCases;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.Translator;
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
        [Inject] GachaLineupDialogUseCase GachaLineupDialogUseCase { get; }
        [Inject] StepupGachaRatioDialogUseCase StepupGachaRatioDialogUseCase { get; }

        bool _isBannerTapped;

        public void OnViewDidLoad()
        {
            // 外から渡されたチケットのIdから
            var useCaseModel = PackShopGachaUseCase.GetPackShopGachaUseCaseModel(Argument.TicketId);

            var viewModel = PackShopGachaViewModelTranslator.Translate(useCaseModel);

            ViewController.SetUp(viewModel);
        }

        void IPackShopGachaViewDelegate.OnBannerTapped(MasterDataId gachaId, GachaType gachaType)
        {
            if (_isBannerTapped) return;

            _isBannerTapped = true;

            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async ct =>
            {
                var targetPosY = ViewController.GetNormalizedPos();

                GachaWireFrame.OnGachaRatioCloseAction = () => ShowPackShopGacha(targetPosY);

                switch (gachaType)
                {
                    case GachaType.Stepup:
                        // ステップアップガシャは運用的に提供割合は表示しない想定のため、ステップごとのコスト確認をしていない
                        var stepupUseCaseModel = await StepupGachaRatioDialogUseCase.GetStepupGachaRatioUseCaseModel(ct, gachaId);
                        var stepupViewModel = StepupGachaRatioDialogViewModelTranslator.TranslateToViewModel(
                            stepupUseCaseModel,
                            OnClickIconDetail);
                        GachaWireFrame.ShowStepupGachaRatioDialogView(gachaId, stepupViewModel, ViewController);
                        break;

                    case GachaType.Medal:
                    case GachaType.Tutorial:
                        var lineupUseCaseModel = await GachaLineupDialogUseCase.GetGachaLineupUseCaseModel(ct, gachaId);
                        var lineupViewModel = GachaLineupDialogViewModelTranslator.TranslateToViewModel(
                            lineupUseCaseModel,
                            OnClickIconDetail);
                        GachaWireFrame.ShowGachaLineUpDialogView(gachaId, lineupViewModel, ViewController);
                        break;

                    default:
                        var ratioUseCaseModel = await GachaRatioDialogUseCase.GetGachaRatioUseCaseModel(ct, gachaId);
                        var ratioViewModel = GachaRatioDialogViewModelTranslator.TranslateToViewModel(
                            ratioUseCaseModel,
                            OnClickIconDetail);
                        GachaWireFrame.ShowGachaRatioDialogView(gachaId, ratioViewModel, ViewController);
                        break;
                }

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
