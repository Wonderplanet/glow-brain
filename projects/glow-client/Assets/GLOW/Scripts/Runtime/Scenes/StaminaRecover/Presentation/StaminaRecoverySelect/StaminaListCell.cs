using System;
using System.Collections;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect
{
    public class StaminaListCell : UICollectionViewCell
    {
        [Header("アイコン")]
        [SerializeField] Image _iconImage;
        [SerializeField] GameObject _adIconObject;
        [SerializeField] Image _resourceAreaIconImage;

        [Header("テキスト")]
        [SerializeField] UIText _textLabel;
        [SerializeField] UIText _amountTextLabel;
        [SerializeField] UIText _adTextLabel;

        [Header("ボタン")]
        [SerializeField] UseButtonStateComponent _useButton;
        [SerializeField] UseButtonStateComponent _adSkipButton;

        [Header("リソース表示")]
        [SerializeField] GameObject _adAreaObject;
        [SerializeField] GameObject _resourceAreaObject;

        // ちょっと良くないかもしれないが、文言はここで管理する
        const string _normalTextFormat = "{0}を1個使用する毎にスタミナを{1}回復します";
        const string _diamondTextFormat = "プリズムを{0}個使用する毎に\nスタミナを{1}回復します";
        const string _adTextFormat = "広告動画を1回視聴する毎にスタミナを{0}回復します";

        const string _adAmountTextFormat = "本日あと<color=#FF0000>{0}</color>回";

        const string _colorFormat_red = "<color=#FF0000>{0}</color>";
        const string _colorFormat_black = "<color=#000000>{0}</color>";

        const string _useButtonTapped = "useButtonTapped";
        const string _adSkipButtonTapped = "adSkipButtonTapped";

        protected void Awake()
        {
            base.Awake();

            AddButton(_useButton.MainButton, _useButtonTapped);
            AddButton(_adSkipButton.MainButton, _adSkipButtonTapped);
        }

        public void Setup(StaminaListCellViewModel viewModel)
        {
            SetUpCellText(viewModel);
            SetUpButton(
                viewModel.AvailableStatus,
                viewModel.RemainingTime,
                viewModel.Availability);
            SetUpIcon(viewModel);
            SetUpResourceArea(viewModel);
        }

        void SetUpCellText(StaminaListCellViewModel viewModel)
        {
            switch (viewModel.AvailableStatus.StaminaRecoveryType)
            {
                case StaminaRecoveryType.Ad:
                case StaminaRecoveryType.AdSkip:
                    _textLabel.SetText(
                        _adTextFormat,
                        viewModel.StaminaEffectValue.Value);
                    break;
                case StaminaRecoveryType.Diamond:
                    _textLabel.SetText(
                        _diamondTextFormat,
                        viewModel.RequiredItemAmount.Value,
                        viewModel.StaminaEffectValue.Value);
                    break;
                case StaminaRecoveryType.Item:
                    _textLabel.SetText(
                        _normalTextFormat,
                        viewModel.Name.Value,
                        viewModel.StaminaEffectValue.Value);
                    break;
            }
        }

        public void SetUpButton(
            StaminaRecoveryAvailableStatus availableStatus,
            RemainingTimeSpan remainingTime,
            StaminaRecoveryAvailability availability)
        {
            var isAdSkip = availableStatus.StaminaRecoveryType == StaminaRecoveryType.AdSkip;

            _useButton.gameObject.SetActive(!isAdSkip);
            _adSkipButton.gameObject.SetActive(isAdSkip);

            var isAvailability = availability == StaminaRecoveryAvailability.Available;

            if (isAdSkip)
            {
                if (!remainingTime.IsEmpty())
                {
                    _adSkipButton.SetUpButton(isAvailability, remainingTime);
                    return;
                }

                _adSkipButton.SetUpButton(isAvailability, RemainingTimeSpan.Empty);
            }
            else
            {
                if (!remainingTime.IsEmpty())
                {
                    _useButton.SetUpButton(isAvailability, remainingTime);
                    return;
                }

                _useButton.SetUpButton(isAvailability, RemainingTimeSpan.Empty);
            }
        }

        void SetUpIcon(StaminaListCellViewModel viewModel)
        {
            var type = viewModel.AvailableStatus.StaminaRecoveryType;

            var isAd = type == StaminaRecoveryType.Ad
                       || type == StaminaRecoveryType.AdSkip;

            _adIconObject.SetActive(isAd);
            _iconImage.gameObject.SetActive(!isAd);

            if (isAd) return;

            if (type == StaminaRecoveryType.Item)
            {
                UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_iconImage,viewModel.IconAssetPath.Value);
            }
        }

        void SetUpHasAmountText(ItemAmount amount)
        {
            var amountText = amount.ToStringSeparated();
            amountText = ZString.Format(amount.Value <= 0
                ? _colorFormat_red
                : _colorFormat_black,
                amountText);
            _amountTextLabel.SetText(amountText);
        }

        void SetUpResourceArea(StaminaListCellViewModel viewModel)
        {
            var status = viewModel.AvailableStatus;

            var isAd = status.StaminaRecoveryType == StaminaRecoveryType.Ad
                       || status.StaminaRecoveryType == StaminaRecoveryType.AdSkip;

            _adAreaObject.SetActive(isAd);
            _resourceAreaObject.SetActive(!isAd);

            if (isAd)
            {
                _adTextLabel.SetText(_adAmountTextFormat, status.BuyAdCount.Value);
            }
            else
            {
                // アイテムの場合はアイコンもセット
                if (status.StaminaRecoveryType == StaminaRecoveryType.Item)
                {
                    UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_resourceAreaIconImage, viewModel.IconAssetPath.Value);
                }

                SetUpHasAmountText(status.ItemAmount);
            }
        }
    }
}
