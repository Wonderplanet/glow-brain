using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public record QuestSelectUseCaseModel(
        MasterDataId CurrentSelectMstQuestGroupId,
        Difficulty CurrentSelectDifficulty,
        IReadOnlyList<QuestSelectContentUseCaseModel> Items)
    {
        public QuestSelectContentUseCaseModel GetCurrentContentModel()
        {
            return Items.FirstOrDefault(
                x => x.GroupId == CurrentSelectMstQuestGroupId,
                QuestSelectContentUseCaseModel.Empty);
        }

        public MasterDataId GetSelectedQuestId()
        {
            var contentContent = GetCurrentContentModel();
            var currentDifficultyModel = contentContent.GetCurrentDifficultyModel();
            
            return currentDifficultyModel.MstQuestId;
        }
    }
}
