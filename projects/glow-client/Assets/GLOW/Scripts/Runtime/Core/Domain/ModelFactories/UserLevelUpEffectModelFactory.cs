using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Sorter;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Domain.ModelFactories
{
    public class UserLevelUpEffectModelFactory : IUserLevelUpEffectModelFactory
    {
        [Inject] IMstUserLevelDataRepository UserLevelDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IPlayerResourceSorter PlayerResourceSorter { get; }

        public UserLevelUpEffectModel Create(
            UserLevelUpResultModel result,
            UserLevel currentLevel,
            UserLevel afterLevel)
        {
            // レベルアップしていない場合は空のモデルを返す
            if(currentLevel == afterLevel)
                return UserLevelUpEffectModel.Empty;

            var maxUserLevelModel = UserLevelDataRepository.GetMaxUserLevelModel();

            var beforeMaxStamina = UserLevelDataRepository.GetUserLevelModel(currentLevel).MaxStamina;
            var afterMaxStamina = UserLevelDataRepository.GetUserLevelModel(afterLevel).MaxStamina;

            var rewards = result.Rewards
                .Select(reward => PlayerResourceModelFactory.Create(
                    reward.RewardModel.ResourceType,
                    reward.RewardModel.ResourceId,
                    reward.RewardModel.Amount))
                .ToList();
            
            rewards = PlayerResourceSorter.Sort(rewards).ToList();

            return new UserLevelUpEffectModel(
                afterLevel,
                rewards,
                beforeMaxStamina,
                afterMaxStamina,
                maxUserLevelModel.Level == afterLevel);
        }
    }
}
