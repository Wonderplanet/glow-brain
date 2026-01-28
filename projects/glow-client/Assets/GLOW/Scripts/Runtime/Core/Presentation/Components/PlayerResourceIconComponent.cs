using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.Serialization;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class PlayerResourceIconComponent : UIObject
    {
        [SerializeField] UIImage _itemIconImage;
        [SerializeField] UIImage _unitIconImage;
        [SerializeField] UIObject _unitIconRoot;
        [SerializeField] IconRarityFrame _rarityFrame;
        [SerializeField] UIText _amountText;
        [SerializeField] UIObject _firstLimitedUI;
        [SerializeField] UIObject _acquiredUI;
        [SerializeField] UIObject _clearTimeUI;
        [SerializeField] UIText _clearTimeText;

        [SerializeField] int _crossMarkFontSize;

        [SerializeField] UIObject _randomRewardUI;
        [SerializeField] UIObject _fixedRewardUI;
        const string AmountTextFormat = "<size={0}>{1}</size>{2}";

        public void Setup(PlayerResourceIconViewModel viewModel)
        {
            Setup(
                viewModel.AssetPath,
                viewModel.RarityFrameType,
                viewModel.Rarity,
                viewModel.Amount,
                viewModel.IsAcquired,
                viewModel.ClearTime,
                viewModel.RewardCategoryLabel);
        }

        public void Setup(
            PlayerResourceIconAssetPath iconAssetPath,
            IconRarityFrameType rarityFrameType,
            Rarity rarity,
            PlayerResourceAmount amount,
            PlayerResourceAcquiredFlag isAcquired,
            StageClearTime clearTime,
            RewardCategoryLabel rewardCategoryLabel)
        {
            var iconImage = rarityFrameType == IconRarityFrameType.Item ? _itemIconImage : _unitIconImage;

            _itemIconImage.IsVisible = rarityFrameType == IconRarityFrameType.Item;
            _unitIconRoot.IsVisible = rarityFrameType == IconRarityFrameType.Unit;

            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                iconImage.Image,
                iconAssetPath.Value,
                () =>
                {
                    if (!iconImage) return;
                    iconImage.Image.SetNativeSize();
                });

            _rarityFrame.Setup(rarityFrameType, rarity);

            SetAmount(amount);

            if (_firstLimitedUI != null)
            {
                _firstLimitedUI.Hidden = rewardCategoryLabel != RewardCategoryLabel.FirstClear;
            }

            if (_randomRewardUI != null)
            {
                _randomRewardUI.Hidden = rewardCategoryLabel != RewardCategoryLabel.Random;
            }

            if (_fixedRewardUI != null)
            {
                _fixedRewardUI.Hidden = rewardCategoryLabel != RewardCategoryLabel.Clear;
            }

            if (_acquiredUI != null)
            {
                _acquiredUI.Hidden = !isAcquired;
            }

            if (_clearTimeUI != null)
            {
                _clearTimeUI.Hidden = clearTime.IsEmpty();
                _clearTimeText.SetText(clearTime.ToString());
            }
        }

        public void SetAmount(PlayerResourceAmount amount)
        {
            _amountText.SetText(AmountTextFormat, _crossMarkFontSize,"×", amount.ToStringSeparated());
            _amountText.Hidden = amount.IsEmpty();
        }
    }
}
