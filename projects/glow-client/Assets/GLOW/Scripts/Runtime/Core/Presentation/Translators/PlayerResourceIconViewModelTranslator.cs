using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Translators
{
    public static class PlayerResourceIconViewModelTranslator
    {
        public static IReadOnlyList<PlayerResourceIconViewModel> ToPlayerResourceIconViewModels(
            IReadOnlyList<PlayerResourceModel> playerResourceModels,
            bool isShowRewardBadge = false)
        {
            return playerResourceModels
                .Select(model => ToPlayerResourceIconViewModel(model, isShowRewardBadge))
                .ToList();
        }

        public static PlayerResourceIconViewModel ToPlayerResourceIconViewModel(PlayerResourceModel playerResourceModel, bool isShowRewardBadge = false)
        {
            return new PlayerResourceIconViewModel(
                playerResourceModel.Id,
                playerResourceModel.Type,
                ToPlayerResourceIconAssetPath(playerResourceModel.Type, playerResourceModel.AssetKey),
                GetIconRarityFrameType(playerResourceModel.Type),
                playerResourceModel.Rarity,
                playerResourceModel.Amount,
                new PlayerResourceAcquiredFlag(playerResourceModel.IsAcquired),
                playerResourceModel.ClearTime,
                ToRewardCategoryLabel(playerResourceModel.RewardCategory, isShowRewardBadge));
        }

        static RewardCategoryLabel ToRewardCategoryLabel(RewardCategory category, bool isShowRewardBadge)
        {
            if (!isShowRewardBadge)
            {
                return RewardCategoryLabel.None;
            }

            return category switch
            {
                RewardCategory.FirstClear => RewardCategoryLabel.FirstClear,
                RewardCategory.SpeedAttackClear => RewardCategoryLabel.SpeedAttackClear,
                RewardCategory.Always => RewardCategoryLabel.Clear,
                RewardCategory.Random => RewardCategoryLabel.Random,
                _ => RewardCategoryLabel.None,
            };
        }

        public static PlayerResourceDetailViewModel ToPlayerResourceDetailViewModel(
            PlayerResourceModel playerResourceModel,
            bool isHideCurrentAmount = false
        )
        {
            return new PlayerResourceDetailViewModel(
                ToPlayerResourceIconViewModel(playerResourceModel, false),
                playerResourceModel.Name,
                playerResourceModel.Description,
                playerResourceModel.Type,
                playerResourceModel.Amount,
                isHideCurrentAmount
            );
        }

        static PlayerResourceIconAssetPath ToPlayerResourceIconAssetPath(ResourceType type,
            PlayerResourceAssetKey assetKey)
        {
            return type switch
            {
                ResourceType.Item => ItemIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.ArtworkFragment => ArtworkFragmentAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.Unit => CharacterIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.Exp => UserExpIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.PaidDiamond => DiamondIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(), // TODO: 一旦無償のものと同様のものを使用。
                ResourceType.FreeDiamond => DiamondIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.Coin => CoinIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.Stamina => StaminaIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.IdleCoin => CoinIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.MissionBonusPoint => MissionBonusPointIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                ResourceType.Emblem => EmblemIconAssetPath.FromAssetKey(assetKey).ToPlayerResourceIconAssetPath(),
                _ => PlayerResourceIconAssetPath.Empty,
            };
        }

        static IconRarityFrameType GetIconRarityFrameType(ResourceType type)
        {
            if (type == ResourceType.Unit)
            {
                return IconRarityFrameType.Unit;
            }

            return IconRarityFrameType.Item;
        }
    }
}
