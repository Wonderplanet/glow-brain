using GLOW.Scenes.EventQuestSelect.Domain;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public static class EventQuestSelectBadgeViewModelTranslator
    {
        public static EventQuestSelectBadgeViewModel ToViewModel(EventQuestSelectBadgeModel model)
        {
            return new EventQuestSelectBadgeViewModel(
                model.IsExistReceivableMission,
                model.IsBoxGachaDrawable
            );
        }
    }
}