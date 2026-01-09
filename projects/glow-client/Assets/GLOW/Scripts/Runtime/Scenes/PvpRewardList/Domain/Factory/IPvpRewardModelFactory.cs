using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpRewardList.Domain.Model;

namespace GLOW.Scenes.PvpRewardList.Domain.Factory
{
    public interface IPvpRewardModelFactory
    {
        IReadOnlyList<IPvpRankingRewardModel> CreateRankingRewardModels(ContentSeasonSystemId sysPvpSeasonId);
        IReadOnlyList<PvpPointRankRewardModel> CreatePvpPointRankRewardModels(ContentSeasonSystemId sysPvpSeasonId);
        IReadOnlyList<PvpTotalScoreRewardModel> CreatePvpTotalScoreRewardModels(ContentSeasonSystemId sysPvpSeasonId);
    }
}