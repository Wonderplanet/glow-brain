using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class StageEndResultDataTranslator
    {
        public static StageEndResultModel ToStageEndResultModel(StageEndResultData data)
        {
            var rewards = data.StageRewards.Select(StageRewardDataTranslator.ToStageRewardResultModel).ToList();
            var userLevelUp = UserLevelUpTranslator.ToUserLevelUpResultModel(data.UserLevel);
            var conditionPacks = data.UsrConditionPacks.Select(UserConditionPackDataTranslator.ToModel).ToList();

            var userArtworkModels = data.UsrArtworks.Select(UserArtworkDataTranslator.ToUserArtworkModel).ToList();
            var userArtworkFragmentModels = data.UsrArtworkFragments.Select(UserArtworkFragmentDataTranslator.ToUserArtworkFragmentModel).ToList();

            var userUnitModels = data.UsrUnits
                .Select(UserUnitDataTranslator.ToUserUnitModel)
                .ToList();

            var userItemModels = data.UsrItems
                .Select(ItemDataTranslator.ToUserItemModel)
                .ToList();

            var enemyDiscoverModels = data.UsrEnemyDiscoveries
                .Select(UserEnemyDiscoverDataTranslator.Translate)
                .ToList();

            var oprCampaignIds = data.OprCampaignIds
                .Select(id => new MasterDataId(id))
                .ToList();

            return new StageEndResultModel(
                rewards,
                userLevelUp,
                conditionPacks,
                userArtworkModels,
                userArtworkFragmentModels,
                userUnitModels,
                userItemModels,
                enemyDiscoverModels,
                oprCampaignIds
            );
        }
    }
}
