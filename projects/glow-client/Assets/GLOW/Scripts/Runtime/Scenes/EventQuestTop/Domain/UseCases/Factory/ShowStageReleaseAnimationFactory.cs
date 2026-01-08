using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class ShowStageReleaseAnimationFactory : IShowStageReleaseAnimationFactory
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        ShowStageReleaseAnimation IShowStageReleaseAnimationFactory.Create(MasterDataId newReleaseMstStageId)
        {
            if (newReleaseMstStageId.IsEmpty()) return ShowStageReleaseAnimation.Empty;
            var mstStageModel = MstStageDataRepository.GetMstStage(newReleaseMstStageId);
            var mstQuestModel = MstQuestDataRepository.GetMstQuestModel(mstStageModel.MstQuestId);

            if(mstQuestModel.QuestType == QuestType.Normal)
            {
                return CreateNormal(newReleaseMstStageId);
            }
            else
            {
                return CreateEvent(newReleaseMstStageId, mstStageModel);
            }
        }

        ShowStageReleaseAnimation CreateNormal(MasterDataId newReleaseMstStageId)
        {
            return new ShowStageReleaseAnimation(newReleaseMstStageId);
        }

        ShowStageReleaseAnimation CreateEvent(MasterDataId newReleaseMstStageId, MstStageModel mstStageModel)
        {
            var mstSettingModel = MstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(mstStageModel.Id);

            if (mstSettingModel.IsEmpty()) return new ShowStageReleaseAnimation(newReleaseMstStageId);

            // イベントステージは開催期間内だったらアニメーション出す
            return CalculateTimeCalculator.IsValidTime(TimeProvider.Now, mstSettingModel.StartAt, mstSettingModel.EndAt)
                ? new ShowStageReleaseAnimation(newReleaseMstStageId)
                : ShowStageReleaseAnimation.Empty;
        }
    }
}
