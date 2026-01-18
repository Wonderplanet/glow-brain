using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleResult.Presentation.ViewModel
{
    public record AdventBattleResultViewModel(
        IReadOnlyList<PlayerResourceIconViewModel> AcquiredPlayerResources,
        AdventBattleScore CurrentScore,
        AdventBattleScore HighScore,
        NewRecordFlag NewRecordFlag,
        AdventBattleResultScoreViewModel AdventBattleResultScoreViewModel,
        RemainingTimeSpan RemainingEventCampaignTimeSpan,
        UserLevelUpResultViewModel UserLevelUpResult,
        RetryAvailableFlag IsRetryAvailable);
}