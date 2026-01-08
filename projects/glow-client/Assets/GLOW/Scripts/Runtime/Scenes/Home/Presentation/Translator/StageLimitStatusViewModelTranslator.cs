using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Translator
{
    public static class StageLimitStatusViewModelTranslator
    {
        public static StageLimitStatusViewModel TranslateViewModel(StageLimitStatus status)
        {
            return new StageLimitStatusViewModel(status.Status,
                status.SeriesLogImageAssetPathList,
                status.Rarities,
                status.PartyUnitNum,
                status.UnitAttackRangeTypes,
                status.UnitRoleTypes,
                status.SummonCost);
        }
    }
}
