using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpRanking.Domain.Models;
using GLOW.Scenes.PvpRanking.Presentation.Constants;
using GLOW.Scenes.PvpRanking.Presentation.ViewModels;

namespace GLOW.Scenes.PvpRanking.Presentation.Translators
{
    public class PvpRankingViewModelTranslator
    {
        public static PvpRankingViewModel ToViewModel(PvpRankingUseCaseModel model)
        {
            if (model.IsEmpty())
            {
                return PvpRankingViewModel.Empty;
            }

            return new PvpRankingViewModel(
                ToElementViewModel(model.CurrentRanking, false),
                ToElementViewModel(model.PreviousRanking, true));
        }

        static PvpRankingElementViewModel ToElementViewModel(PvpRankingElementUseCaseModel model, bool isPrev)
        {
            var otherUserViewModels = model.OtherUserModels
                .Select(ToPvpRankingOtherUserViewModel)
                .ToList();

            return new PvpRankingElementViewModel(
                otherUserViewModels,
                ToPvpRankingMyselfUserViewModel(model.MyselfUserModel, isPrev));
        }

        static PvpRankingOtherUserViewModel ToPvpRankingOtherUserViewModel(PvpRankingOtherUserUseCaseModel model)
        {
            return new PvpRankingOtherUserViewModel(
                model.UserName,
                model.TotalPoint,
                !model.EmblemAssetKey.IsEmpty() ?
                    EmblemIconAssetPath.FromAssetKey(model.EmblemAssetKey) :
                    EmblemIconAssetPath.Empty,
                !model.UnitAssetKey.IsEmpty() ?
                    CharacterIconAssetPath.FromAssetKey(model.UnitAssetKey) :
                    CharacterIconAssetPath.Empty,
                model.Rank,
                model.IsMyself,
                model.RankClassType,
                model.RankLevel,
                model.PvpUserRankStatus);
        }

        static PvpRankingMyselfUserViewModel ToPvpRankingMyselfUserViewModel(
            PvpRankingMyselfUserUseCaseModel model,
            bool isPrev)
        {
            if (model.IsEmpty())
            {
                return PvpRankingMyselfUserViewModel.Empty;
            }
            return new PvpRankingMyselfUserViewModel(
                model.UserName,
                model.TotalPoint,
                !model.EmblemAssetKey.IsEmpty() ?
                    EmblemIconAssetPath.FromAssetKey(model.EmblemAssetKey) :
                    EmblemIconAssetPath.Empty,
                !model.UnitAssetKey.IsEmpty() ?
                    CharacterIconAssetPath.FromAssetKey(model.UnitAssetKey) :
                    CharacterIconAssetPath.Empty,
                model.Rank,
                model.RankClassType,
                model.RankLevel,
                model.CalculatingRankings,
                ToPvpRankingMyselfUserViewStatus(model, isPrev),
                model.PvpUserRankStatus);
        }

        static PvpRankingMyselfUserViewStatus ToPvpRankingMyselfUserViewStatus(
            PvpRankingMyselfUserUseCaseModel model,
            bool isPrev)
        {
            if (model.IsExcludeRanking)
            {
                return PvpRankingMyselfUserViewStatus.ExcludeRanking;
            }
            
            if (!model.IsInEntry)
            {
                return isPrev ?
                    PvpRankingMyselfUserViewStatus.EmptyPrevRanking :
                    PvpRankingMyselfUserViewStatus.EmptyCurrentRanking;
            }

            return !model.IsAchieveRanking ?
                PvpRankingMyselfUserViewStatus.NotAchieveRanking :
                PvpRankingMyselfUserViewStatus.Normal;
        }
    }
}
