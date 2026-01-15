using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserLevelUpTranslator
    {
        public static UserLevelUpResultModel ToUserLevelUpResultModel(UserLevelUpData data)
        {
            var rewards = data.UsrLevelReward.Select(ToUserLevelRewardResultModel).ToList();

            return new UserLevelUpResultModel(
                new UserExp(data.BeforeExp),
                new UserExp(data.AfterExp),
                rewards);
        }

        static UsrLevelRewardResultModel ToUserLevelRewardResultModel(UsrLevelRewardData data)
        {

            return new UsrLevelRewardResultModel(new UserLevel(data.Level),  RewardDataTranslator.Translate(data.Reward));
        }
    }
}
