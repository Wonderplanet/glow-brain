using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.UserLevelUp.Domain.Model;
using Zenject;

namespace GLOW.Scenes.UserLevelUp.Domain.UseCase
{
    public class ShowUserLevelUpInfoUseCase
    {
        [Inject] IUserLevelUpCacheRepository UserLevelUpCacheRepository { get; }
        [Inject] IUserLevelUpEffectModelFactory UserLevelUpEffectModelFactory { get; }
        [Inject] IUserExpGainModelsFactory UserExpGainModelsFactory { get; }
        
        public UserLevelUpInfoModel GetUserLevelUpInfo()
        {
            var prevUserLevel = UserLevelUpCacheRepository.GetPrevUserLevel();
            var prevExp = UserLevelUpCacheRepository.GetPrevExp();

            if (prevUserLevel.IsEmpty() || prevExp.IsEmpty())
            {
                return UserLevelUpInfoModel.Empty;
            }
            
            var userLevelUpResultCacheModel = MergeUserLevelUpResultModels();
            var userExpGainModels = UserExpGainModelsFactory.CreateUserExpGainModels(
                userLevelUpResultCacheModel,
                prevUserLevel,
                prevExp);

            var afterUserLevel = userExpGainModels.Max(model => model.Level);
            
            var userLevelUpEffectModel = UserLevelUpEffectModelFactory.Create(
                userLevelUpResultCacheModel,
                prevUserLevel,
                afterUserLevel);

            var lastGainModel = userExpGainModels.LastOrDefault(UserExpGainModel.Empty);
            var currentExp = lastGainModel.EndExp;
            var nextLevelExp = lastGainModel.NextLevelExp;
            
            // キャッシュをクリア
            UserLevelUpCacheRepository.Clear();
            
            return new UserLevelUpInfoModel(
                userLevelUpEffectModel, 
                userExpGainModels, 
                currentExp, 
                nextLevelExp,
                userLevelUpResultCacheModel.IsExpChange());
        }
        
        UserLevelUpResultModel MergeUserLevelUpResultModels()
        {
            var userLevelUpResultCacheModels = UserLevelUpCacheRepository.GetUserLevelUpResultHistoryModels();
            var minExp = userLevelUpResultCacheModels.Min(x => x.BeforeExp);
            var maxExp = userLevelUpResultCacheModels.Max(x => x.AfterExp);
            var rewards = new List<UsrLevelRewardResultModel>();
            foreach (var model in userLevelUpResultCacheModels)
            {
                rewards.AddRange(model.Rewards);
            }
            
            return new UserLevelUpResultModel(minExp, maxExp, rewards);
        }
    }
}