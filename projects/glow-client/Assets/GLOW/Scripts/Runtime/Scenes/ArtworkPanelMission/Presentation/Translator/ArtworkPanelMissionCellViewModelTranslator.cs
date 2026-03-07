using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.Translator
{
    public static class ArtworkPanelMissionCellViewModelTranslator
    {
        public static ArtworkPanelMissionCellViewModel ToCellViewModel(ArtworkPanelMissionCellModel model)
        {
            return new ArtworkPanelMissionCellViewModel(
                model.MstMissionLimitedTermId,
                model.MissionType,
                model.MissionStatus,
                model.MissionProgress,
                model.CriterionCount,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                    model.ArtworkFragmentPlayerResourceModel),
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                    model.OtherRewardPlayerResourceModel),
                model.MissionDescription,
                model.DestinationScene);
        }
    }
}