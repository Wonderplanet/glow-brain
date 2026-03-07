using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.ArtworkPanelMission.Domain.Model;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.Translator
{
    public static class ArtworkPanelMissionViewModelTranslator
    {
        public static ArtworkPanelMissionViewModel ToViewModel(ArtworkPanelMissionModel model)
        {
            var fetchResultViewModel = ArtworkPanelMissionFetchResultViewModelTranslator.ToViewModel(
                model.ArtworkPanelMissionFetchResultModel);
            
            return new ArtworkPanelMissionViewModel(
                model.MstArtworkPanelMissionId,
                model.MstEventId,
                ArtworkPanelViewModelTranslator.ToTranslate(model.ArtworkPanelModel),
                model.RemainingTimeSpan,
                fetchResultViewModel);
        }
        
        
    }
}