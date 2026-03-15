using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    public record FinishResultViewModel(
        IReadOnlyList<PlayerResourceIconViewModel> AcquiredPlayerResources,
        ResultScoreModel ResultScoreModel,
        RemainingTimeSpan RemainingEventCampaignTimeSpan,
        RetryAvailableFlag RetryAvailableFlag);
}
