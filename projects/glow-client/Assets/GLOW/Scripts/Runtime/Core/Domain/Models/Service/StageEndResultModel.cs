using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models
{
    public record StageEndResultModel(
        IReadOnlyList<StageRewardResultModel> Rewards,
        UserLevelUpResultModel UserLevelUp,
        IReadOnlyList<UserConditionPackModel> ConditionPacks,
        IReadOnlyList<UserArtworkModel> UserArtworkModels,
        IReadOnlyList<UserArtworkFragmentModel> UserArtworkFragmentModels,
        IReadOnlyList<UserUnitModel> UserUnitModels,
        IReadOnlyList<UserItemModel> UserItemModels,
        IReadOnlyList<UserEnemyDiscoverModel> UserEnemyDiscoverModels,
        IReadOnlyList<MasterDataId> OprCampaignIds)
    {
        public static StageEndResultModel Empty { get; } = new(
            new List<StageRewardResultModel>(),
            UserLevelUpResultModel.Empty,
            new List<UserConditionPackModel>(),
            new List<UserArtworkModel>(),
            new List<UserArtworkFragmentModel>(),
            new List<UserUnitModel>(),
            new List<UserItemModel>(),
            new List<UserEnemyDiscoverModel>(),
            new List<MasterDataId>());
    }
}
