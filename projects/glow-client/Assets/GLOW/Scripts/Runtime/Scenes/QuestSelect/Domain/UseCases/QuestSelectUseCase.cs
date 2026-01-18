using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Domain
{
    public class QuestSelectUseCase
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }

        [Inject] IQuestSelectUseCaseModelItemFactory QuestSelectUseCaseModelItemFactory { get; }
        [Inject] IQuestReleaseCheckSampleFinder QuestReleaseCheckSampleFinder { get; }

        public QuestSelectUseCaseModel GetQuestSelectUseCaseModels(MasterDataId initialSelectedMstQuestId)
        {
            if (initialSelectedMstQuestId.IsEmpty())
            {
                initialSelectedMstQuestId = PreferenceRepository.CurrentHomeTopSelectMstQuestId;
            }

            if (initialSelectedMstQuestId.IsEmpty())
            {
                initialSelectedMstQuestId = GetDefaultMstQuestId();
            }

            var mstQuest = MstQuestDataRepository.GetMstQuestModel(initialSelectedMstQuestId);
            return GetQuestSelectUseCaseModels(mstQuest);
        }

        QuestSelectUseCaseModel GetQuestSelectUseCaseModels(MstQuestModel initialSelectedMstQuestModel)
        {
            var groupId = initialSelectedMstQuestModel.GroupId;
            var difficulty = initialSelectedMstQuestModel.Difficulty;

            var mstQuests = MstQuestDataRepository.GetMstQuestModels();

            // NOTE: 難易度ノーマルのみの取得で、そこから別難易度設定の情報も取得可能なのでWhereで絞る
            var questItems = mstQuests
                .Where(m => m.QuestType == QuestType.Normal)
                .Where(m => m.Difficulty == Difficulty.Normal)
                .Select(m =>
                {
                    var sampleMstStageModel = QuestReleaseCheckSampleFinder.GetSampleAtMstStageModel(m);
                    return QuestSelectUseCaseModelItemFactory.CreateQuestSelectContentUseCaseModel(m, sampleMstStageModel);
                })
                .ToList();

            var selectedUseCaseModel = questItems.FirstOrDefault(
                i => i.GroupId == groupId,
                QuestSelectContentUseCaseModel.Empty);

            if (!selectedUseCaseModel.IsEmpty)
            {
                var updatedUseCaseModel = selectedUseCaseModel.CopyWithUpdatedCurrentDifficulty(difficulty);
                questItems.Replace(selectedUseCaseModel, updatedUseCaseModel);
            }

            return new QuestSelectUseCaseModel(groupId, difficulty, questItems);
        }

        MasterDataId GetDefaultMstQuestId()
        {
            var defaultMstStage = GameRepository.GetGameFetch().StageModels.Count <= 0
                ? MstStageDataRepository.GetMstStages()
                    .First(m => !m.Id.ToString().Contains("develop")) //developの単語が仮
                : MstStageDataRepository.GetMstStage(GameRepository.GetGameFetch().StageModels
                    .OrderBy(s => s.MstStageId).First().MstStageId);

            return defaultMstStage.MstQuestId;
        }
    }
}
