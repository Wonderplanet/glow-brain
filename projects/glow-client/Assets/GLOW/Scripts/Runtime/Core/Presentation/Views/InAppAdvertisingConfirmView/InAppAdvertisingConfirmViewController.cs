using System;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.ViewModels;
using UIKit;

namespace GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView
{
    public class InAppAdvertisingConfirmViewController : UIViewController<InAppAdvertisingConfirmView>
    {
        Action _onConfirmAction;

        public void SetUp(
            IAARewardFeatureType rewardFeatureType,
            string rewardName,
            int rewardValue,
            int leftCountValue,
            Action onConfirmAction)
        {
            ActualView.SetUp(rewardFeatureType, rewardName, rewardValue, leftCountValue);
            _onConfirmAction = onConfirmAction;
        }

        public void SetUpProductPlateComponent(PlayerResourceIconViewModel productIconViewModel, ProductName rewardName)
        {
            ActualView.SetUpProductPlateComponent(productIconViewModel, rewardName);
        }

        [UIAction]
        public void OnConfirm()
        {
            _onConfirmAction?.Invoke();
            Dismiss();
        }

        [UIAction]
        public void OnClose()
        {
            Dismiss();
        }
    }
}
