using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGameSpecialRule.Domain.Models;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeStageInfoUseCaseModel(
        IReadOnlyList<HomeStageInfoEnemyUseCaseModel> EnemyCharacters,
        IReadOnlyList<HomeStageInfoArtworkFragmentUseCaseModel> ArtworkFragmentResource,
        IReadOnlyList<HomeStageInfoRewardUseCaseModel> PlayerResources,
        InGameSpecialRuleModel InGameSpecialRule,
        HomeStageInfoSpeedAttackUseCaseModel SpeedAttack,
        InGameDescription InGameDescription);
}
