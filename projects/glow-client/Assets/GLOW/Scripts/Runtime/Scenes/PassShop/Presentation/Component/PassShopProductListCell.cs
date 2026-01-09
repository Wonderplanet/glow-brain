using System;
using System.Collections;
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using ModestTree;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PassShop.Presentation.Component
{
    public class PassShopProductListCell : UIObject
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _startDateText;
        [SerializeField] UIText _endDateText;
        [SerializeField] UIText _passProductDescriptionText;
        [SerializeField] UIObject _expirationObject;
        [SerializeField] UIImage _passImage;
        [SerializeField] PassEffectListComponent _passEffectListComponent;
        [SerializeField] PassRewardsComponent _passImmediatelyRewardsComponent;
        [SerializeField] PassRewardsComponent _passDailyRewardsComponent;
        [SerializeField] Button _purchaseButton;
        [SerializeField] Button _detailButton;
        [SerializeField] UIText _priceText;
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] UIObject _noticeBadgeObject;

        public Button.ButtonClickedEvent OnInfoButtonClicked => _detailButton.onClick;
        public Button.ButtonClickedEvent OnPurchaseButtonClicked => _purchaseButton.onClick;

        public void SetTitleText(PassProductName productName)
        {
            _titleText.SetText(productName.Value);
        }

        public void SetDisplayExpirationVisible(DisplayExpirationFlag displayExpirationFlag)
        {
            _expirationObject.Hidden = !displayExpirationFlag;
        }

        public void LoadPassImage(PassIconAssetPath passIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _passImage.Image,
                passIconAssetPath.Value);
        }

        public void SetStartDateText(PassStartAt startAt)
        {
            _startDateText.SetText(startAt.ToFormattedString());
        }

        public void SetEndDateText(PassEndAt endAt)
        {
            _endDateText.SetText(endAt.ToFormattedString());
        }

        public void SetPassDescriptionText(PassDurationDay durationDay)
        {
            var passDescription = ZString.Format(
                "{0}日間さまざまな特典が得られるサービスです",
                durationDay.ToString());
            _passProductDescriptionText.SetText(passDescription);
        }

        public void SetupPassEffectListComponent(
            IReadOnlyList<PassEffectViewModel> effectViewModels)
        {
            _passEffectListComponent.SetupPassEffectList(effectViewModels);
        }

        public void SetupPassImmediatelyRewardsComponent(
            PassDurationDay durationDay,
            IReadOnlyList<PlayerResourceIconViewModel> rewards,
            Action<PlayerResourceIconViewModel> iconTapAction)
        {
            if (rewards.IsEmpty())
            {
                _passImmediatelyRewardsComponent.Hidden = true;
                return;
            }

            _passImmediatelyRewardsComponent.SetupRewards(
                ShopPassRewardType.Immediately,
                durationDay,
                rewards,
                iconTapAction);
        }

        public void SetupPassDailyRewardsComponent(
            PassDurationDay durationDay,
            IReadOnlyList<PlayerResourceIconViewModel> rewards,
            Action<PlayerResourceIconViewModel> iconTapAction)
        {
            if (rewards.IsEmpty())
            {
                _passDailyRewardsComponent.Hidden = true;
                return;
            }

            _passDailyRewardsComponent.SetupRewards(
                ShopPassRewardType.Daily,
                durationDay,
                rewards,
                iconTapAction);
        }

        public void SetPriceText(RawProductPriceText rawProductPriceText)
        {
            _priceText.SetText(rawProductPriceText.ToString());
        }

        public void SetPurchaseButtonVisible(bool visible)
        {
            // 残り時間が空だったら購入ボタンを表示する
            _purchaseButton.gameObject.SetActive(visible);
        }

        public void SetNoticeBadgeVisible(bool visible)
        {
            // 残り時間が空だったら赤バッジを表示する
            _noticeBadgeObject.IsVisible = visible;
        }

        public void StartRemainingTimeCountDown(
            RemainingTimeSpan remainingTimeSpan)
        {
            StartCoroutine(UpdateRemainingTime(remainingTimeSpan));
        }

        public void SetRemainingTimeText(RemainingTimeSpan remainingTimeSpan)
        {
            _remainingTimeText.SetText(TimeSpanFormatter.FormatRemaining(remainingTimeSpan));
        }

        IEnumerator UpdateRemainingTime(
            RemainingTimeSpan remainingTimeSpan)
        {
            var remainingTime = remainingTimeSpan;
            while (!remainingTime.IsMinus())
            {
                SetRemainingTimeText(remainingTime);

                yield return new WaitForSeconds(1);
                remainingTime = remainingTime.Subtract(TimeSpan.FromSeconds(1));
            }

            SetPurchaseButtonVisible(true);
            SetNoticeBadgeVisible(true);
        }
    }
}
