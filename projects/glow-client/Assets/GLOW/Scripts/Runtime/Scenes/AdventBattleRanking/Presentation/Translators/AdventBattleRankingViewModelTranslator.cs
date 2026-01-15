using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattleRanking.Domain.Models;
using GLOW.Scenes.AdventBattleRanking.Presentation.Constants;
using GLOW.Scenes.AdventBattleRanking.Presentation.ValueObjects;
using GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattleRanking.Presentation.Translators
{
    public class AdventBattleRankingViewModelTranslator
    {
        public static AdventBattleRankingViewModel ToViewModel(AdventBattleRankingUseCaseModel model)
        {
            if (model.IsEmpty())
            {
                return AdventBattleRankingViewModel.Empty;
            }

            return new AdventBattleRankingViewModel(
                ToElementViewModel(model.CurrentRanking));
        }

        static AdventBattleRankingElementViewModel ToElementViewModel(AdventBattleRankingElementUseCaseModel model)
        {
            var otherUserViewModels = model.OtherUserModels
                .Select(ToAdventBattleRankingOtherUserViewModel)
                .ToList();

            return new AdventBattleRankingElementViewModel(
                otherUserViewModels,
                ToAdventBattleRankingMyselfUserViewModel(model.MyselfUserModel),
                model.AdventBattleName);
        }

        static AdventBattleRankingOtherUserViewModel ToAdventBattleRankingOtherUserViewModel(
            AdventBattleRankingOtherUserUseCaseModel model)
        {
            return new AdventBattleRankingOtherUserViewModel(
                model.UserName,
                model.MaxScore,
                !model.EmblemAssetKey.IsEmpty() ?
                    EmblemIconAssetPath.FromAssetKey(model.EmblemAssetKey) :
                    EmblemIconAssetPath.Empty,
                !model.UnitAssetKey.IsEmpty() ?
                    CharacterIconAssetPath.FromAssetKey(model.UnitAssetKey) :
                    CharacterIconAssetPath.Empty,
                model.Rank,
                model.IsMyself,
                model.RankType,
                model.RankLevel);
        }

        static AdventBattleRankingMyselfUserViewModel ToAdventBattleRankingMyselfUserViewModel(
            AdventBattleRankingMyselfUserUseCaseModel model)
        {
            return new AdventBattleRankingMyselfUserViewModel(
                model.UserName,
                model.MaxScore,
                !model.EmblemAssetKey.IsEmpty() ?
                    EmblemIconAssetPath.FromAssetKey(model.EmblemAssetKey) :
                    EmblemIconAssetPath.Empty,
                !model.UnitAssetKey.IsEmpty() ?
                    CharacterIconAssetPath.FromAssetKey(model.UnitAssetKey) :
                    CharacterIconAssetPath.Empty,
                model.Rank,
                model.RankType,
                model.RankLevel,
                model.CalculatingRankings,
                ToAdventBattleRankingMyselfUserViewStatus(model));
        }

        static AdventBattleRankingMyselfUserViewStatus ToAdventBattleRankingMyselfUserViewStatus(
            AdventBattleRankingMyselfUserUseCaseModel model)
        {
            if (model.IsExcludeRanking)
            {
                return AdventBattleRankingMyselfUserViewStatus.ExcludeRanking;
            }
            
            if (!model.IsInEntry)
            {
                return AdventBattleRankingMyselfUserViewStatus.EmptyCurrentRanking;
            }

            return !model.IsAchieveRanking ?
                AdventBattleRankingMyselfUserViewStatus.NotAchieveRanking :
                AdventBattleRankingMyselfUserViewStatus.Normal;

        }
    }
}
