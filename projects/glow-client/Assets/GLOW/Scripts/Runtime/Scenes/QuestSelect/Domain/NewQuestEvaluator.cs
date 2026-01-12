using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public class NewQuestEvaluator : INewQuestEvaluator
    {
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        NewQuestFlag INewQuestEvaluator.IsNewQuest(QuestOpenStatus questOpenStatus, MstQuestModel mstQuestModel)
        {
            if(questOpenStatus != QuestOpenStatus.Released) return NewQuestFlag.False;
            return new NewQuestFlag(!PreferenceRepository.SelectedMstQuestIds.Contains(mstQuestModel.Id));
        }

        NewQuestFlag INewQuestEvaluator.IsNewQuestAtEvent(QuestOpenStatus questOpenStatus, MstQuestModel mstQuestModel)
        {
            if(questOpenStatus != QuestOpenStatus.Released) return NewQuestFlag.False;
            var userModelAndMstStageModel = GameRepository.GetGameFetch().UserStageEventModels
                .Join(
                    MstStageDataRepository.GetMstStages(),
                    user => user.MstStageId,
                    mst => mst.Id,
                    (user, mst) => new { user, mst });

            // userDataで何かしらあればTrue
            var anyPlayedStage = userModelAndMstStageModel
                .Any(uAndm => uAndm.mst.MstQuestId == mstQuestModel.Id);
            return new NewQuestFlag(!anyPlayedStage);
        }

    }
}
