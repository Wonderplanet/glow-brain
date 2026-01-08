using System;
using System.Collections;
using System.Threading;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade;
using GLOW.Scenes.StaminaRecover.Presentation.Translator;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect
{
    public class StaminaRecoverySelectPresenter : IStaminaRecoverySelectViewDelegate
    {
        [Inject] StaminaRecoverySelectViewController ViewController { get; }
        [Inject] StaminaRecoverySelectViewController.Argument Argument { get; }
        [Inject] GetStaminaRecoveryItemUseCase GetStaminaRecoveryItemUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] StaminaRecoverExecutionUseCase StaminaRecoverExecutionUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeUseCases UseCases { get; }
        [Inject] GetUserMaxStaminaUseCase GetUserMaxStaminaUseCase { get; }

        CancellationToken _cancellationToken;
        Stamina _userMaxStamina;

        void IStaminaRecoverySelectViewDelegate.OnViewDidLoad()
        {
            ViewController.InitializeView();
            RefreshView();
        }

        void IStaminaRecoverySelectViewDelegate.OnUseButtonTapped(
            MasterDataId mstItemId,
            StaminaRecoveryAvailableStatus availableStatus,
            Stamina staminaEffectValue)
        {
            ShowStaminaConfirmDialog(mstItemId, availableStatus, staminaEffectValue);
        }

        void IStaminaRecoverySelectViewDelegate.OnUpdateStaminaResetTime(
            StaminaListCell cell,
            StaminaRecoveryAvailableStatus availableStatus,
            RemainingTimeSpan remainingTime,
            StaminaRecoveryAvailability availability)
        {
            ViewController.View
                .StartCoroutine(ViewController.AutoUpdateCell(
                    cell,
                    availableStatus,
                    remainingTime,
                    availability));
        }

        void IStaminaRecoverySelectViewDelegate.OnClose()
        {
            Close();
        }

        void RefreshView()
        {
            var staminaRecoveryItems =
                GetStaminaRecoveryItemUseCase.GetStaminaRecoveryItems();
            var viewModel = StaminaRecoverySelectViewModelTranslator
                .Translate(Argument.IsStaminaShortage, staminaRecoveryItems);
            ViewController.SetUpView(viewModel);

            _userMaxStamina = GetUserMaxStaminaUseCase.GetUserMaxStamina();
        }

        void ShowStaminaConfirmDialog(
            MasterDataId mstItemId,
            StaminaRecoveryAvailableStatus availableStatus,
            Stamina staminaEffectValue)
        {
            switch (availableStatus.StaminaRecoveryType)
            {
                case StaminaRecoveryType.Item:
                    OnRecoverAtItem(mstItemId);
                    break;

                case StaminaRecoveryType.Diamond:
                    OnRecoverAtDiamond(staminaEffectValue);
                    break;

                case StaminaRecoveryType.Ad:
                case StaminaRecoveryType.AdSkip:
                    OnRecoverAtAd(staminaEffectValue, availableStatus);
                    break;
            }
        }

        void OnRecoverAtItem(MasterDataId mstItemId)
        {
            var argument = new StaminaTradeViewController.Argument(
                mstItemId,
                () => ViewController.Dismiss(),
                RefreshView);
            var controller = ViewFactory.Create<StaminaTradeViewController,
                StaminaTradeViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        void OnRecoverAtDiamond(Stamina staminaEffectValue)
        {
            var controller = ViewFactory.Create<StaminaDiamondRecoverConfirmViewController>();
            controller.OnCancel = RefreshView;
            controller.OnConfirm = Close;

            var stamina = CalculateStaminaBeforeAndAfter(staminaEffectValue);

            controller.SetUpStaminaText(stamina.Item1, stamina.Item2);

            ViewController.PresentModally(controller);
        }

        void OnRecoverAtAd(Stamina staminaEffectValue, StaminaRecoveryAvailableStatus availableStatus)
        {
            if (GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty())
            {
                var vc = CreateAdConfirmView(staminaEffectValue, availableStatus);
                ViewController.PresentModally(vc);
            }
            else
            {
                DoAsync.Invoke(_cancellationToken, ScreenInteractionControl, async (cancellationToken) =>
                {
                    //await内部でAPI call, UserParameterModelの更新を行う
                    await StaminaRecoverExecutionUseCase.BuyStaminaFromAd(cancellationToken);
                    AdStaminaRecoverCompleted(staminaEffectValue);
                });
            }
        }

        InAppAdvertisingConfirmViewController CreateAdConfirmView(
            Stamina staminaEffectValue,
            StaminaRecoveryAvailableStatus availableStatus)
        {
            //広告表示
            var vc = ViewFactory.Create<InAppAdvertisingConfirmViewController>();
            vc.SetUp(
                IAARewardFeatureType.StaminaRecover,
                string.Empty, //未使用
                staminaEffectValue.Value,
                availableStatus.BuyAdCount.Value,
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
                        AdStaminaRecoverCompleted(staminaEffectValue);
                    });
                });

            var stamina = CalculateStaminaBeforeAndAfter(staminaEffectValue);

            vc.SetUpStaminaText(stamina.Item1, stamina.Item2);

            return vc;
        }

        (int, int) CalculateStaminaBeforeAndAfter(Stamina stamina)
        {
            var beforeStamina = UseCases.GetUserParameter().Stamina.Value;
            var afterStamina = beforeStamina + stamina.Value;
            if(afterStamina > _userMaxStamina.Value) afterStamina = _userMaxStamina.Value;

            return (beforeStamina, afterStamina);
        }

        void AdStaminaRecoverCompleted(Stamina recoverStamina)
        {
            Close();
            HomeHeaderDelegate.UpdateStatus();
            ShowCompleteView(recoverStamina.Value);
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

        void Close()
        {
            ViewController.Dismiss(true, ViewController.OnDismissAction);
        }
    }
}
