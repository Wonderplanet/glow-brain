using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Presentation.ViewModel
{
    public record PvpTopViewModel(
        ContentSeasonSystemId SysPvpSeasonId,
        PvpTopRankingState PvpTopRankingState,
        PvpTopUserState PvpTopUserState,
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<PvpTopOpponentViewModel> OpponentViewModels,
        PartyName PartyName,
        PvpOpponentRefreshCoolTime PvpOpponentRefreshCoolTime,
        InGameSpecialRuleUnitStatusFlag HasInGameSpecialRuleUnitStatus,
        PvpTopNextTotalScoreRewardViewModel PvpTopNextTotalScoreRewardViewModel
        );
}
