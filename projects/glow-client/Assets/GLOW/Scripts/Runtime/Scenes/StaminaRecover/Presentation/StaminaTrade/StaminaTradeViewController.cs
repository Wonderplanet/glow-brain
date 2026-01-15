using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade
{
    public class StaminaTradeViewController : UIViewController<StaminaTradeView>
    {
        public record Argument(MasterDataId MstItemId, Action OnCompleted, Action OnRefresh);
        [Inject] IStaminaTradeViewDelegate ViewDelegate { get; }

        StaminaTradeViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpView(StaminaTradeViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel, UpdateView);
        }

        void UpdateView()
        {
            // スタミナ回復後のスタミナ計算、更新
            ActualView.SetStaminaTexts(_viewModel.CurrentUserStamina, _viewModel.EffectValue, _viewModel.MaxStamina);

            // 確認テキスト更新
            var maxRecoverableStamina = _viewModel.MaxStamina - _viewModel.CurrentUserStamina;
            ActualView.SetConfirmText(_viewModel.Name, ActualView.SelectedAmount, _viewModel.EffectValue, maxRecoverableStamina);
        }

        [UIAction]
        void OnUseButtonTapped()
        {
            ViewDelegate.OnBuyStaminaButtonTapped(_viewModel.MstItemId, ActualView.SelectedAmount);
        }

        [UIAction]
        void OnItemIconTapped()
        {
            ViewDelegate.OnItemIconTapped(_viewModel.ItemIconViewModel);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
