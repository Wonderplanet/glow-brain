using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public interface IQuestSelectViewDelegate
    {
        void OnViewDidLoad();
        void OnDifficultySelected(MasterDataId mstGroupQuestId, Difficulty difficulty);
        void ApplySelectedQuest(MasterDataId mstQuestId);
    }
}
