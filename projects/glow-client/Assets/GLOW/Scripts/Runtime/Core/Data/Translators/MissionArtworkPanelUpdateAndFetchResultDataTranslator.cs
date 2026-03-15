using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class MissionArtworkPanelUpdateAndFetchResultDataTranslator
    {
        public static MissionArtworkPanelUpdateAndFetchResultModel ToMissionArtworkPanelUpdateAndFetchResultModel(
            MissionArtworkPanelUpdateAndFetchResultData data)
        {
            var userMissionLimitedTermModels = data.UsrMissionLimitedTerms
                .Select(UserMissionLimitedTermDataTranslator.ToUserMissionLimitedTermModel)
                .ToList();

            var userArtworkFragmentModels = data.UsrArtworkFragments
                .Select(UserArtworkFragmentDataTranslator.ToUserArtworkFragmentModel)
                .ToList();

            return new MissionArtworkPanelUpdateAndFetchResultModel(
                userMissionLimitedTermModels,
                userArtworkFragmentModels);
        }
    }
}