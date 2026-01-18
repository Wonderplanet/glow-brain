using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IUserExpGainModelsFactory
    {
        IReadOnlyList<UserExpGainModel> CreateUserExpGainModels(
            UserLevelUpResultModel userLevelUpResultModel,
            UserLevel prevUserLevel,
            UserExp prevExp);
    }
}