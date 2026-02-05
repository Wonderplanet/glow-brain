using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record VictoryResultViewModel(
        CharacterStandImageAssetPath CharacterStandImageAssetPath,
        IReadOnlyList<UserExpGainViewModel> UserExpGains,
        UserLevelUpResultViewModel UserLevelUpResult,
        IReadOnlyList<PlayerResourceIconViewModel> AcquiredPlayerResources,
        IReadOnlyList<IReadOnlyList<PlayerResourceIconViewModel>> AcquiredPlayerResourcesGroupedByStaminaRap,
        IReadOnlyList<ArtworkFragmentAcquisitionViewModel> ArtworkFragmentAcquisitionViewModels,
        ResultSpeedAttackViewModel SpeedAttack,
        RemainingTimeSpan RemainingEventCampaignTimeSpan,
        RetryAvailableFlag RetryAvailableFlag);
}
