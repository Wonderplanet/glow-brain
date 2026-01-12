using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.StaminaRecover.Domain;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm
{
    public class StaminaDiamondRecoverConfirmPresenter : IStaminaDiamondRecoverConfirmViewDelegate
    {

        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] StaminaRecoverConfirmUseCase StaminaRecoverConfirmUseCase { get; }
        [Inject] StaminaRecoverExecutionUseCase ExecutionUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] IHomeUseCases UseCases { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] GetUserMaxStaminaUseCase GetUserMaxStaminaUseCase { get; }

        CancellationToken CancellationToken => _viewController.View.GetCancellationTokenOnDestroy();
        StaminaDiamondRecoverConfirmViewController _viewController;

        StaminaDiamondRecoverConfirmViewModel _viewModel;

        void IStaminaDiamondRecoverConfirmViewDelegate.OnViewDidLoad(StaminaDiamondRecoverConfirmViewController viewController)
        {
            _viewController = viewController;
            UpdateDisplayPlayerResource();
        }

        StaminaDiamondRecoverConfirmViewModel CreateViewModel(StaminaRecoverConfirmUseCaseModel model)
        {
            var afterDiamond =
                DiamondCalculator.CalculateAfterDiamonds(
                    model.PaidDiamond,
                    model.FreeDiamond,
                    model.ConsumeDiamondValue);
            return new StaminaDiamondRecoverConfirmViewModel(
                model.IsShortage,
                model.ConsumeDiamondValue,
                model.RecoverValue,
                model.PaidDiamond,
                afterDiamond.paid,
                model.FreeDiamond,
                afterDiamond.free);
        }

        void IStaminaDiamondRecoverConfirmViewDelegate.SpecificCommerceButtonTapped()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IStaminaDiamondRecoverConfirmViewDelegate.OnRecoverAtDiamond()
        {
            if (IsMaxStamina())
            {
                var maxStamina = GetUserMaxStaminaUseCase.GetUserMaxStamina().Value;
                var message = $"スタミナの所持上限は{maxStamina}となっているため、これ以上回復できません。";
                MessageViewUtil.ShowMessageWithOk(
                    "確認",
                    message,
                    "",
                    () => _viewController.Dismiss());//ここではOnClose叩かなくて良い
                return;
            }

            DoAsync.Invoke(CancellationToken, ScreenInteractionControl, async (cancellationToken) =>
            {
                //await内部でAPI call, UserParameterModelの更新を行う
                await ExecutionUseCase.BuyStaminaFromDiamond(cancellationToken);
                HomeHeaderDelegate.UpdateStatus();
                _viewController.OnConfirm?.Invoke();

                _viewController.Dismiss();//ここではOnClose叩かなくて良い
                ShowCompleteView(_viewModel.RecoverValue.Value);
            });
        }

        void IStaminaDiamondRecoverConfirmViewDelegate.TransitionToDiamondShopView()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopItem))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            // ヘッダー更新をコールバックで行う
            var argument = new DiamondPurchaseViewController.Argument(UpdateDisplayPlayerResource);

            var viewController =
                ViewFactory.Create<DiamondPurchaseViewController, DiamondPurchaseViewController.Argument>(argument);

            _viewController.PresentModally(viewController);
        }
        void UpdateDisplayPlayerResource()
        {
            // ダイアログの表示更新
            var model = StaminaRecoverConfirmUseCase.GetModel();
            _viewModel = CreateViewModel(model);
            _viewController.SetViewModel(_viewModel);

            // プレイヤーリソース表示更新
            HomeHeaderDelegate.UpdateStatus();
        }

        bool IsMaxStamina()
        {
            var userParameterModel = UseCases.GetUserParameter();
            return GetUserMaxStaminaUseCase.GetUserMaxStamina().Value <= userParameterModel.Stamina.Value;
        }

        void ShowCompleteView(int recoverValue)
        {
            var message = $"スタミナを{recoverValue}回復しました。";
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                message,
                "",
                () => { }
            );
        }
    }
}
