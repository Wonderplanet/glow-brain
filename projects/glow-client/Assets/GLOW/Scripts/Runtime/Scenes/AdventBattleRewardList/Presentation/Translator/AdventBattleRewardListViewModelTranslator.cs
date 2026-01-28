using System.Linq;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRewardList.Domain.Model;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Translator
{
    public class AdventBattleRewardListViewModelTranslator
    {
        public static AdventBattleRewardListViewModel ToAdventBattleRewardListViewModel(
            AdventBattleRewardListModel model)
        {
            var personalScore = model.PersonalScore.IsEmpty()
                ? AdventBattleScore.Zero
                : model.PersonalScore;
            
            var raidTotalScore = model.RaidTotalScore.IsEmpty()
                ? AdventBattleRaidTotalScore.Zero
                : model.RaidTotalScore;
            
            return new AdventBattleRewardListViewModel(
                model.BattleType,
                personalScore,
                raidTotalScore,
                model.RankType,
                model.RankLevel,
                model.RemainingTimeSpan,
                model.PersonalRankingRewardModels.Select(
                    AdventBattlePersonalRewardCellViewModelTranslator.ToAdventBattlePersonalRewardCellViewModel)
                    .ToList(),
                model.PersonalRankRewardModels.Select(
                        AdventBattlePersonalRewardCellViewModelTranslator.ToAdventBattlePersonalRewardCellViewModel)
                    .ToList(),
                model.RaidTotalScoreRewardModels.Select(
                    AdventBattleRaidTotalScoreRewardCellViewModelTranslator.ToAdventBattleRaidTotalScoreRewardCellViewModel)
                    .ToList());
        }
    }
}