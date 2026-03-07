using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class UserExpGainModelsFactory : IUserExpGainModelsFactory
    {
        [Inject] IMstUserLevelDataRepository UserLevelDataRepository { get; }
        
        IReadOnlyList<UserExpGainModel> IUserExpGainModelsFactory.CreateUserExpGainModels(
            UserLevelUpResultModel userLevelUpResultModel, 
            UserLevel prevUserLevel,
            UserExp prevExp)
        {
            var userLevel = prevUserLevel;
            var userExp = prevExp;
            var additionalUserExp = userLevelUpResultModel.AfterExp - userLevelUpResultModel.BeforeExp;
            var maxUserLevelModel = UserLevelDataRepository.GetMaxUserLevelModel();
            var userExpGains = new List<UserExpGainModel>();

            while (userLevel <= maxUserLevelModel.Level && additionalUserExp >= UserExp.Zero)
            {
                var userLevelModel = UserLevelDataRepository.GetUserLevelModel(userLevel);
                var nextLevelExp = userLevelModel.NextLevelExp;

                var nextUserExp = UserExp.Min(userExp + additionalUserExp, nextLevelExp);

                var userExpGain = new UserExpGainModel(
                    userLevel,
                    userLevelModel.ToRelativeUserExp(userExp),
                    userLevelModel.ToRelativeUserExp(nextUserExp),
                    userLevelModel.RelativeNextLevelExp);

                userExpGains.Add(userExpGain);

                additionalUserExp = userExp + additionalUserExp - nextLevelExp;
                userExp = nextUserExp;
                userLevel += 1;
            }

            return userExpGains;
        }
    }
}