using System;
using System.Threading;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemBox.Domain.Evaluator;
using GLOW.Scenes.ItemBox.Domain.UseCases;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.Translator;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade
{
    public class StaminaTradePresenter : IStaminaTradeViewDelegate
    {
        [Inject] StaminaTradeViewController ViewController { get; }
        [Inject] StaminaTradeViewController.Argument Argument { get; }
        [Inject] ActiveItemUseCase ActiveItemUseCase { get; }
        [Inject] ActiveItemWireFrame ActiveItemWireFrame { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CreateStaminaTradeUseCase CreateStaminaTradeUseCase { get; }
        [Inject] ConsumeItemUseCase ConsumeItemUseCase { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] StaminaTradeConfirmWireFrame StaminaTradeConfirmWireFrame { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] GetUserMaxStaminaUseCase GetUserMaxStaminaUseCase { get; }
        [Inject] GetStaminaUseCase GetStaminaUseCase { get; }

        StaminaTradeViewModel _viewModel;
        ItemAmount _selectedAmount;
        Stamina _tradeBeforeStamina;

        void IStaminaTradeViewDelegate.OnViewDidLoad()
        {
            var useCaseModel = CreateStaminaTradeUseCase.CreateStaminaTradeUseCaseModel(Argument.MstItemId);
            var viewModel = StaminaTradeViewModelTranslator.Translate(useCaseModel);
            _viewModel = viewModel;
            ViewController.SetUpView(viewModel);
        }

        void IStaminaTradeViewDelegate.OnBuyStaminaButtonTapped(MasterDataId mstItemId, ItemAmount amount)
        {
            if (!ActiveItemUseCase.IsActiveItem(mstItemId))
            {
                ActiveItemWireFrame.ShowInactiveItemMessage(ViewController, Argument.OnRefresh);
                return;
            }

            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async ct =>
            {
                try
                {
                    _tradeBeforeStamina = GetStaminaUseCase.GetUserStamina().Stamina;

                    await ConsumeItemUseCase.ConsumeItem(
                        ViewController.ActualView.destroyCancellationToken,
                        Argument.MstItemId,
                        amount);

                    _selectedAmount = amount;

                    HomeHeaderDelegate.UpdateStatus();
                    ShowCompleteView();

                    Argument.OnCompleted();
                    ViewController.Dismiss();
                }
                catch (UserStaminaFullException)
                {
                    StaminaTradeConfirmWireFrame.BackToHomeTopUserStaminaFull(
                        ()=> ViewController.Dismiss());
                }
                catch (ItemAmountIsNotEnoughException)
                {
                    StaminaTradeConfirmWireFrame.BackToHomeTopItemNotOwned(
                        ()=> ViewController.Dismiss());
                }
                catch (UserStaminaExceedsLimitException)
                {
                    StaminaTradeConfirmWireFrame.BackToHomeTopStaminaExceedsLimit(
                        () => ViewController.Dismiss());
                }
                catch (MstNotFoundException)
                {
                    StaminaTradeConfirmWireFrame.BackToHomeTopItemNotFound(
                        () => ViewController.Dismiss());
                }
                catch (InvalidParameterException)
                {
                    StaminaTradeConfirmWireFrame.BackToHomeTopInvalidParameter(
                        () => ViewController.Dismiss());
                }
            });
        }

        void IStaminaTradeViewDelegate.OnItemIconTapped(PlayerResourceIconViewModel itemIconViewModel)
        {
            ItemDetailWireFrame.ShowItemDetailView(itemIconViewModel, ViewController);
        }

        void IStaminaTradeViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        void ShowCompleteView()
        {
            var staminaEffectValue = GetActualRecoverStamina();
            var message = $"スタミナを{staminaEffectValue}回復しました。";
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                message,
                "",
                () =>
                {
                }
            );
        }

        int GetActualRecoverStamina()
        {
            var staminaEffectValue = _viewModel.EffectValue.Value * _selectedAmount.Value;
            var maxStamina = GetUserMaxStaminaUseCase.GetUserMaxStamina().Value;
            var currentStamina = _tradeBeforeStamina.Value;

            var possibleRecoverStamina = maxStamina - currentStamina;
            if(staminaEffectValue > possibleRecoverStamina) staminaEffectValue = possibleRecoverStamina;

            if(staminaEffectValue > maxStamina) staminaEffectValue = maxStamina;

            return staminaEffectValue;
        }
    }
}
