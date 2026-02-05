using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public class SessionResumeStateRegistrationUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] IMstStageDataRepository StageDataRepository { get; }
        [Inject] IMstQuestDataRepository QuestDataRepository { get; }

        public void SaveResumableState(InGameContentType inGameContentType, MasterDataId mstId)
        {
            ResumableStateRepository.Save(GetResumableStateModel(inGameContentType, mstId));
        }

        ResumableStateModel GetResumableStateModel(InGameContentType inGameContentType, MasterDataId mstId)
        {
            if (inGameContentType == InGameContentType.AdventBattle)
            {
                return new ResumableStateModel(SceneViewContentCategory.AdventBattle, mstId, MasterDataId.Empty);
            }
            else if (inGameContentType == InGameContentType.Pvp)
            {
                return new ResumableStateModel(SceneViewContentCategory.Pvp, mstId, MasterDataId.Empty);
            }
            else
            {
                return GetAtStageInGameContentType(mstId);
            }
        }
        
        ResumableStateModel GetAtStageInGameContentType(MasterDataId mstStageId)
        {
            //ここでやるか検討。inject増えがち。
            var mstStageModel = StageDataRepository.GetMstStage(mstStageId);
            var mstQuestModel = QuestDataRepository.GetMstQuestModel(mstStageModel.MstQuestId);

            // EventだけMstQuestModel.GroupIdを使う
            return mstQuestModel.QuestType switch
            {
                QuestType.Normal => new ResumableStateModel(SceneViewContentCategory.MainStage, mstStageId, MasterDataId.Empty),
                QuestType.Event => new ResumableStateModel(SceneViewContentCategory.EventStage, mstQuestModel.GroupId,mstQuestModel.MstEventId),
                QuestType.Enhance => new ResumableStateModel(SceneViewContentCategory.EnhanceStage, mstStageId, MasterDataId.Empty),
                _ => new ResumableStateModel(SceneViewContentCategory.None, mstStageId, MasterDataId.Empty)
            };
        }
    }
}