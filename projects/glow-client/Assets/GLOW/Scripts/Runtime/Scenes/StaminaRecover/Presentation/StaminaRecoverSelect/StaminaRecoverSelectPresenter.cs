using System;
using System.Collections;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect
{
    public class StaminaRecoverSelectPresenter : IStaminaRecoverSelectViewDelegate
    {
        [Inject] StaminaRecoverSelectViewController ViewController { get; }
        [Inject] StaminaRecoverSelectViewController.Argument Argument { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] StaminaRecoverSelectUseCase StaminaRecoverSelectUseCase { get; }
        [Inject] StaminaRecoverExecutionUseCase StaminaRecoverExecutionUseCase { get; }
        [Inject] PlaceholderFactory<StaminaDiamondRecoverConfirmViewController> DiamondRecoverConfirmViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewController HomeViewController { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CalculateReceivableStaminaTimeUseCase CalculateReceivableStaminaTimeUseCase { get; }
        [Inject] IHomeUseCases UseCases { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }

        CancellationToken CancellationToken => ViewController.View.GetCancellationTokenOnDestroy();
        BuyStaminaAdCount _remainingAdRecoverCount;

        void IStaminaRecoverSelectViewDelegate.OnViewDidLoad()
        {
            UpdateView();
            //広告回復の待ち時間コルーチン作成
            ViewController.ActualView.StartCoroutine(AdRecoverStamina());
        }

        void UpdateView()
        {
            //viewModel作成
            var useCaseModel = StaminaRecoverSelectUseCase.GetModel();
            _remainingAdRecoverCount = useCaseModel.RemainingAdRecoverCount;
            ViewController.SetViewModel(CreateViewModel(Argument.Type, useCaseModel));
        }

        StaminaRecoverSelectViewModel CreateViewModel(
            StaminaRecoverSelectType openType,
            StaminaRecoverSelectUseCaseModel useCaseModel)
        {
            return new StaminaRecoverSelectViewModel(
                openType == StaminaRecoverSelectType.Buy ? "スタミナ購入" : "スタミナ不足",
                openType == StaminaRecoverSelectType.Buy ? "" : "スタミナが不足しています。\nスタミナを回復しますか？",
                useCaseModel.AdStaminaRecover,
                useCaseModel.RemainingAdRecoverCount,
                useCaseModel.AdRecoverStamina,
                useCaseModel.ConsumeDiamondValue,
                useCaseModel.DiamondRecoverStamina,
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(useCaseModel.HeldAdSkipPassInfoModel)
            );
        }

        IEnumerator AdRecoverStamina()
        {
            var receivableTimeModel = CalculateReceivableStaminaTimeUseCase.CalcReceivableTime();
            TimeSpan intervalMinute = receivableTimeModel.ReceivableStaminaTime.Value;
            ViewController.UpdateAdRecoverInterval(receivableTimeModel.StaminaRecoveryFlag.Value, "");

            //広告回復の待ち時間
            while (TimeSpan.Zero < intervalMinute)
            {
                var waitTime = intervalMinute.Milliseconds * 0.001f;
                //IdleIncentiveQuickReceiveWindowPresenterと同じFormatなので、統合したい(Converter.cs作る？)
                var model = CalculateReceivableStaminaTimeUseCase.CalcReceivableTime();
                ViewController.UpdateAdRecoverInterval(
                    model.StaminaRecoveryFlag.Value,
                    ZString.Format("{0:mm\\:ss}", intervalMinute));
                yield return new WaitForSeconds(waitTime);
                intervalMinute = model.ReceivableStaminaTime.Value;
            }
        }

        void IStaminaRecoverSelectViewDelegate.OnRecoverAtAd(Stamina recoverStamina)
        {
            if (IsMaxStamina())
            {
                MessageViewUtil.ShowMessageWithOk(
                    "確認",
                    "スタミナが最大のため、回復できません。",
                    "",
                    OnClose);
                return;
            }

            if (GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty())
            {
                var vc = CreateAdConfirmView(recoverStamina);
                ViewController.PresentModally(vc);
            }
            else
            {
                DoAsync.Invoke(CancellationToken, ScreenInteractionControl, async (cancellationToken) =>
                {
                    //await内部でAPI call, UserParameterModelの更新を行う
                    await StaminaRecoverExecutionUseCase.BuyStaminaFromAd(cancellationToken);
                    AdStaminaRecoverCompleted(recoverStamina);
                });
            }
        }

        InAppAdvertisingConfirmViewController CreateAdConfirmView(Stamina recoverStamina)
        {
            //広告表示
            var vc = ViewFactory.Create<InAppAdvertisingConfirmViewController>();
            vc.SetUp(
                IAARewardFeatureType.StaminaRecover,
                string.Empty, //未使用
                recoverStamina.Value,
                _remainingAdRecoverCount.Value,
                () =>
                {
                    DoAsync.Invoke(ViewController.View, async ct =>
                    {
                        var result =
                            await InAppAdvertisingWireframe.ShowAdAsync(IAARewardFeatureType.StaminaRecover, ct);
                        //広告視聴キャンセルされたら何もしない
                        if (result == AdResultType.Cancelled) return;

                        //await内部でAPI call, UserParameterModelの更新を行う
                        await StaminaRecoverExecutionUseCase.BuyStaminaFromAd(ct);
                        AdStaminaRecoverCompleted(recoverStamina);
                    });
                });

            return vc;
        }

        void AdStaminaRecoverCompleted(Stamina recoverStamina)
        {
            OnClose();
            HomeHeaderDelegate.UpdateStatus();
            ShowCompleteView(recoverStamina.Value);
        }

        bool IsMaxStamina()
        {
            var userParameterModel = UseCases.GetUserParameter();
            return userParameterModel.MaxStamina.Value <= userParameterModel.Stamina.Value;
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

        void IStaminaRecoverSelectViewDelegate.OnRecoverAtDiamond()
        {
            var controller = DiamondRecoverConfirmViewFactory.Create();

            controller.OnCancel = () =>
            {
                var selectViewController =
                    ViewFactory.Create<StaminaRecoverSelectViewController, StaminaRecoverSelectViewController.Argument>(
                        new StaminaRecoverSelectViewController.Argument(Argument.Type));
                selectViewController.OnDismissAction = ViewController.OnDismissAction;
                HomeViewController.PresentModally(selectViewController);
            };
            controller.OnConfirm = () =>
            {
                ViewController.OnDismissAction?.Invoke();
            };
            ViewController.PresentModally(controller);
        }

        void IStaminaRecoverSelectViewDelegate.OnClose()
        {
            OnClose();
        }

        void OnClose()
        {
            ViewController.Dismiss(true, ViewController.OnDismissAction);
        }
    }
}
