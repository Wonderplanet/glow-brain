using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpTop.Presentation.View.PvpTicketConfirm
{
    public class PvpTicketConfirmViewController : UIViewController<PvpTicketConfirmView>
    {
        public record Argument(ItemAmount ItemAmount, PvpItemChallengeCost PvpItemChallengeCost);

        [Inject] Argument Args { get; }
        public Action OnApplyAction { get; set; }
        public Action OnShopTransitAction { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            bool isSufficient = Args.PvpItemChallengeCost.Value <= Args.ItemAmount.Value;

            ActualView.SetUpTitleText(isSufficient);
            ActualView.SetUpDescriptionText(Args.PvpItemChallengeCost);
            ActualView.SetUpAmountTexts(Args.ItemAmount, Args.PvpItemChallengeCost);
            ActualView.SetUpTransitAreaActive(!isSufficient);
            ActualView.SetUpApplyButtonActive(isSufficient);
            ActualView.SetInsufficientTextActive(!isSufficient);
        }

        [UIAction]
        public void OnClose()
        {
            Dismiss();
        }

        [UIAction]
        public void OnApply()
        {
            OnApplyAction?.Invoke();
            Dismiss();
        }

        [UIAction]
        public void OnShop()
        {
            OnShopTransitAction?.Invoke();
            Dismiss();
        }
    }
}
