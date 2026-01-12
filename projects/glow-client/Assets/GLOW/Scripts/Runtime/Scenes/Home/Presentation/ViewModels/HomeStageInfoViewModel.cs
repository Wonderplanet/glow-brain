using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.InGameSpecialRule.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeStageInfoViewModel(
        IReadOnlyList<HomeStageInfoEnemyCharacterViewModel> EnemyCharacters,
        IReadOnlyList<PlayerResourceIconViewModel> ArtworkFragmentResource,
        IReadOnlyList<PlayerResourceIconViewModel> PlayerResources,
        InGameSpecialRuleViewModel InGameSpecialRuleViewModel,
        HomeStageInfoSpeedAttackViewModel SpeedAttackViewModel,
        InGameDescription InGameDescription);
}
