using System.Linq;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PvpPreviousSeasonResult.Domain.Models;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.ViewModels;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Presenters
{
    public static class PvpPreviousSeasonResultTranslator
    {
        public static PvpPreviousSeasonResultViewModel Translate(PvpPreviousSeasonResultAnimationModel model)
        {
            return new PvpPreviousSeasonResultViewModel(
                model.PvpRankClassType,
                model.RankClassLevel,
                model.Point,
                model.Ranking,
                model.PvpRewards
                    .Select(resource => PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(resource))
                    .ToList()
                );
        }
    }
}
