using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.Models;

namespace GLOW.Scenes.AdventBattle.Domain.Model
{
    public record AdventBattleInfoModel(
        IReadOnlyList<HomeStageInfoEnemyUseCaseModel> EnemyList, 
        InGameSpecialRuleModel InGameSpecialRuleModel,
        IReadOnlyList<PlayerResourceModel> RewardList,
        InGameDescription InGameDescription);
}