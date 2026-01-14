using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;

namespace GLOW.Core.Presentation.Modules
{
    public static class PlayerResourceMerger
    {
        public static IReadOnlyList<PlayerResourceIconViewModel> Merge(IReadOnlyList<PlayerResourceIconViewModel> models)
        {
            return models
                .GroupBy(reward => new { reward.Id, reward.ResourceType, reward.RewardCategoryLabel })
                .Select(group =>
                {
                    var amount = group.Any(reward => !reward.Amount.IsEmpty())
                        ? new PlayerResourceAmount(group.Sum(reward => reward.Amount.Value))
                        : PlayerResourceAmount.Empty;
                    return new PlayerResourceIconViewModel(
                        group.Key.Id,
                        group.Key.ResourceType,
                        group.First().AssetPath,
                        group.First().RarityFrameType,
                        group.First().Rarity,
                        amount,
                        group.First().IsAcquired,
                        group.First().ClearTime,
                        group.Key.RewardCategoryLabel);
                })
                .ToList();
        }

        public static IReadOnlyList<CommonReceiveResourceViewModel> MergeCommonReceiveResourceModel(IReadOnlyList<CommonReceiveResourceViewModel> models)
        {
            return models
                .GroupBy(reward => new
                {
                    reward.UnreceivedRewardReasonType,
                    reward.PlayerResourceIconViewModel.Id,
                    reward.PlayerResourceIconViewModel.ResourceType,
                    reward.PlayerResourceIconViewModel.RewardCategoryLabel
                })
                .Select(group =>
                {
                    var amount = group.Any(reward => !reward.PlayerResourceIconViewModel.Amount.IsEmpty())
                        ? new PlayerResourceAmount(group.Sum(reward => reward.PlayerResourceIconViewModel.Amount.Value))
                        : PlayerResourceAmount.Empty;

                    var unreceivedRewardReasonType = group
                        .Select(reward => reward.UnreceivedRewardReasonType)
                        .Distinct()
                        .OrderByDescending(u =>
                            new List<UnreceivedRewardReasonType>()
                            {
                                // TODO: この並びで良いか判断にこまる。要素が上書きされることは基本良くない
                                UnreceivedRewardReasonType.None,
                                UnreceivedRewardReasonType.ResourceLimitReached,
                                UnreceivedRewardReasonType.ResourceOverflowDiscarded,
                                UnreceivedRewardReasonType.InvalidData,
                                UnreceivedRewardReasonType.SentToMessage,
                            }.IndexOf(u))
                        .First();

                    var iconViewModel =  new PlayerResourceIconViewModel(
                        group.Key.Id,
                        group.Key.ResourceType,
                        group.First().PlayerResourceIconViewModel.AssetPath,
                        group.First().PlayerResourceIconViewModel.RarityFrameType,
                        group.First().PlayerResourceIconViewModel.Rarity,
                        amount,
                        group.First().PlayerResourceIconViewModel.IsAcquired,
                        group.First().PlayerResourceIconViewModel.ClearTime,
                        group.Key.RewardCategoryLabel);
                    var preConversionIconViewModel = group.First().PreConversionPlayerResourceIconViewModel;

                    return new CommonReceiveResourceViewModel(
                        unreceivedRewardReasonType,
                        iconViewModel,
                        preConversionIconViewModel);
                })
                .ToList();
        }
    }
}
