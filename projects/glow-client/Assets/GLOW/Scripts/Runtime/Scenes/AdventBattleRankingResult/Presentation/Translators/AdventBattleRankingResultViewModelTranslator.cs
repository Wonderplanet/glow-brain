using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using AdventBattleRankingResultModel = GLOW.Scenes.AdventBattleRankingResult.Domain.Models.AdventBattleRankingResultModel;
namespace GLOW.Scenes.AdventBattleRankingResult.Presentation.Translators
{
    public class AdventBattleRankingResultViewModelTranslator
    {
        public static AdventBattleRankingResultViewModel ToViewModel(AdventBattleRankingResultModel model)
        {
            var rewardList = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.RewardList);
            return new AdventBattleRankingResultViewModel(
                model.RankType,
                model.RankLevel,
                model.Rank,
                model.Score,
                rewardList,
                model.AdventBattleType,
                model.EnemyImageAssetPath,
                model.AdventBattleName);
        }
    }
}
