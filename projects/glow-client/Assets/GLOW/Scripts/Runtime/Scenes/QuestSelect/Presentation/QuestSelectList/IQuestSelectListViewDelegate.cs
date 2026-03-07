using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestSelect.Presentation;

namespace GLOW.Scenes.QuestSelectList.Presentation
{
    public interface IQuestSelectListViewDelegate
    {
        void OnClose();
        void OnViewDidLoad();
        void ApplySelectedQuest(MasterDataId mstQuestId);
    }
}
