using System.Collections.Generic;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.AdventBattleResult.Domain.Factory
{
    public interface IAdventBattleResultScoreModelFactory
    {
        AdventBattleResultScoreModel CreateAdventBattleScoreModel(
            AdventBattleScore beforeTotalScore,
            AdventBattleScore afterTotalScore,
            InGameScoreModel inGameScoreModel,
            IReadOnlyList<AdventBattleRewardModel> rankRewards);
    }
}