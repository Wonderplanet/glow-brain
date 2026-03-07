using System.Linq;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.Translator
{
    public static class ArtworkPanelMissionFetchResultViewModelTranslator
    {
        public static ArtworkPanelMissionFetchResultViewModel ToViewModel(ArtworkPanelMissionFetchResultModel model)
        {
            return new ArtworkPanelMissionFetchResultViewModel(model.ArtworkPanelMissionListCellModels
                    .Select(ArtworkPanelMissionCellViewModelTranslator.ToCellViewModel)
                    .ToList());
        }
    }
}