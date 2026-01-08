using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ShopBuyConform.Presentation.Component;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView
{
    public class InAppAdvertisingConfirmView : UIView
    {
        [SerializeField] UIText _descriptionText;
        [SerializeField] UIText _attentionText;
        [SerializeField] UIObject _attentionObj;
        [SerializeField] ShopProductPlateComponent _productPlateComponent;  // ショップ用

        const string ShopFormat = "動画広告を視聴して\n「{0}」\nを入手することができます。";
        const string IdleIncentiveText = "動画広告を視聴して\n報酬を獲得できます。";
        const string StaminaRecoverFormat = "動画広告を視聴して\nスタミナを{0}回復できます。";
        const string QuestChallengeFormat = "動画広告を視聴して\n挑戦回数を{0}回復できます。";
        // ガシャ・コンティニューは独自に実装済

        const string AttentionFormat = "本日あと{0}回";

        public void SetUp(IAARewardFeatureType rewardFeatureType, string rewardName, int rewardValue, int leftCountValue)
        {
            switch (rewardFeatureType)
            {
                case IAARewardFeatureType.Shop:
                    _descriptionText.SetText(ShopFormat, rewardName);
                    break;
                case IAARewardFeatureType.IdleIncentive:
                    _descriptionText.SetText(IdleIncentiveText);
                    break;
                case IAARewardFeatureType.StaminaRecover:
                    _descriptionText.SetText(StaminaRecoverFormat, rewardValue);
                    break;
                case IAARewardFeatureType.QuestChallenge:
                    _descriptionText.SetText(QuestChallengeFormat, rewardValue);
                    break;
            }
            _attentionText.SetText(AttentionFormat, leftCountValue);
            _attentionObj.IsVisible = rewardFeatureType != IAARewardFeatureType.Shop;  // ショップは表示しない

        }

        public void SetUpProductPlateComponent(PlayerResourceIconViewModel productIconViewModel, ProductName rewardName)
        {
            _productPlateComponent.IsVisible = !productIconViewModel.IsEmpty();
            if(_productPlateComponent.IsVisible)
            {
                _productPlateComponent.Setup(productIconViewModel, rewardName, productIconViewModel.Amount, DiscountRate.Empty);
            }
        }

    }
}
