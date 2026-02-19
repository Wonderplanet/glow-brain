using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PvpTop.Domain;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.PvpTop.Presentation.ViewModel;

namespace GLOW.Scenes.PvpTop.Presentation.Translator
{
    public static class PvpTopViewModelTranslator
    {
        public static PvpTopViewModel Translate(PvpTopUseCaseModel model)
        {
            var opponentViewModels = model.OpponentModels
                .Select(TranslatePvpTopOpponentViewModel)
                .ToList();

            return new PvpTopViewModel(
                SysPvpSeasonId: model.SysPvpSeasonId,
                PvpTopRankingState: model.PvpTopRankingState,
                PvpTopUserState: model.PvpTopUserState,
                RemainingTimeSpan: model.RemainingTimeSpan,
                OpponentViewModels: opponentViewModels,
                PartyName: model.PartyName,
                PvpOpponentRefreshCoolTime: model.PvpOpponentRefreshCoolTime,
                HasInGameSpecialRuleUnitStatus: model.HasInGameSpecialRuleUnitStatus,
                PvpTopNextTotalScoreRewardViewModel: TranslatePvpTopNextTotalScoreRewardViewModel(
                    model.PvpTopNextTotalScoreRewardModel)
            );
        }

        public static PvpTopOpponentViewModel TranslatePvpTopOpponentViewModel(
            PvpTopOpponentModel opponentSelectStatusModel)
        {
            return new PvpTopOpponentViewModel(
                UserId: opponentSelectStatusModel.UserId,
                UserName: opponentSelectStatusModel.UserName,
                CharacterIconAssetPath: opponentSelectStatusModel.CharacterIconAssetPath,
                EmblemIconAssetPath: opponentSelectStatusModel.EmblemIconAssetPath,
                Point: opponentSelectStatusModel.Point,
                TotalPoint: opponentSelectStatusModel.TotalPoint,
                PvpUserRankStatus: opponentSelectStatusModel.PvpUserRankStatus,
                PartyUnits: opponentSelectStatusModel.PartyUnits.Select(TranslatePartyUnit).ToList(),
                TotalPartyStatus: opponentSelectStatusModel.TotalPartyStatus,
                TotalPartyStatusUpperArrowFlag: opponentSelectStatusModel.TotalPartyStatusUpperArrowFlag
            );
        }

        static PvpTopOpponentPartyUnitViewModel TranslatePartyUnit(PvpTopOpponentPartyUnitModel partyUnit)
        {
            return new PvpTopOpponentPartyUnitViewModel(
                partyUnit.CharacterIconAssetPath,
                partyUnit.RoleType,
                partyUnit.Color,
                partyUnit.Rarity,
                partyUnit.Level,
                partyUnit.Grade);
        }

        static PvpTopNextTotalScoreRewardViewModel TranslatePvpTopNextTotalScoreRewardViewModel(
            PvpTopNextTotalScoreRewardModel model)
        {
            if (model.IsEmpty())
            {
                return PvpTopNextTotalScoreRewardViewModel.Empty;
            }

            return new PvpTopNextTotalScoreRewardViewModel(
                NextTotalScoreReward: PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                    model.NextTotalScoreReward),
                NextTotalScore: model.NextTotalScore
            );
        }
    }
}
