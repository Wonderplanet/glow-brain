using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpPreviousSeasonResult.Domain.Models;
using GLOW.Scenes.PvpTop.Domain.Model;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpTopUseCaseModel(
        ContentSeasonSystemId SysPvpSeasonId,
        PvpTopRankingState PvpTopRankingState,
        PvpTopUserState PvpTopUserState,
        RemainingTimeSpan RemainingTimeSpan,
        IReadOnlyList<PvpTopOpponentModel> OpponentModels,
        PartyName PartyName,
        PvpOpponentRefreshCoolTime PvpOpponentRefreshCoolTime,
        ViewableRankingFromCalculatingFlag IsViewableRankingFromCalculating,
        PvpPreviousSeasonResultAnimationModel PvpPreviousSeasonResultAnimationModel,
        PvpEndAt PvpEndAt,
        InGameSpecialRuleUnitStatusFlag HasInGameSpecialRuleUnitStatus,
        PvpReceivedTotalScoreRewardsModel PvpReceivedTotalScoreRewardsModel,
        PvpTopNextTotalScoreRewardModel PvpTopNextTotalScoreRewardModel
    )
    {
        public static PvpTopUseCaseModel Empty { get; } = new(
            ContentSeasonSystemId.Empty,
            PvpTopRankingState.Empty,
            PvpTopUserState.Empty,
            RemainingTimeSpan.Empty,
            new List<PvpTopOpponentModel>(),
            PartyName.Empty,
            PvpOpponentRefreshCoolTime.Empty,
            ViewableRankingFromCalculatingFlag.False,
            PvpPreviousSeasonResultAnimationModel.Empty,
            PvpEndAt.Empty,
            InGameSpecialRuleUnitStatusFlag.False,
            PvpReceivedTotalScoreRewardsModel.Empty,
            PvpTopNextTotalScoreRewardModel.Empty
        );
    };
}
