using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using Zenject;

namespace GLOW.Core.Domain.UseCases
{
    public class SceneWireFrameUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IQuestOpenStatusEvaluator QuestOpenStatusEvaluator { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }

        public SceneWireFrameUseCaseModel Get()
        {
            var stateModel= ResumableStateRepository.Get();
            if(stateModel.IsEmpty()) return SceneWireFrameUseCaseModel.Empty;

            if (stateModel.Category == SceneViewContentCategory.EventStage)
            {
                return CreateUseCaseModelAtEvent(stateModel);
            }

            if (stateModel.Category == SceneViewContentCategory.Pvp)
            {
                return CreateUseCaseModelAtPvp(stateModel);
            }

            if (stateModel.Category == SceneViewContentCategory.AdventBattle)
            {
                return CreateUseCaseModelAtAdventBattle(stateModel);
            }

            return new SceneWireFrameUseCaseModel(
                stateModel.Category,
                stateModel.MstId,
                stateModel.MstEventId,
                QuestOpenStatus.Released);

        }


        SceneWireFrameUseCaseModel CreateUseCaseModelAtEvent(ResumableStateModel stateModel)
        {
            var mstQuest = MstQuestDataRepository.GetMstQuestModelsByQuestGroup(stateModel.MstId).First();

            //開催期間外だったらEmptyを返す
            if (mstQuest.QuestType == QuestType.Event && !IsOpenEvent(stateModel.MstEventId))
            {
                return SceneWireFrameUseCaseModel.Empty;
            }
            else
            {
                return new SceneWireFrameUseCaseModel(
                    stateModel.Category,
                    stateModel.MstId,
                    stateModel.MstEventId,
                    QuestOpenStatusEvaluator.Evaluate(mstQuest));
            }
        }

        bool IsOpenEvent(MasterDataId mstEventId)
        {
            var mstEvent = MstEventDataRepository.GetEvent(mstEventId);
            return CalculateTimeCalculator.IsValidTime(
                   TimeProvider.Now,
                   mstEvent.StartAt,
                   mstEvent.EndAt);
        }

        SceneWireFrameUseCaseModel CreateUseCaseModelAtPvp(ResumableStateModel stateModel)
        {
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;

            var isTimeRangeValid = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                sysPvpSeasonModel.StartAt.Value,
                sysPvpSeasonModel.EndAt.Value);

            //対象のpvpが開催期間外だったらEmptyを返す
            if (sysPvpSeasonModel.IsEmpty() ||
                sysPvpSeasonModel.Id != stateModel.MstId ||
                !isTimeRangeValid)
            {
                return new SceneWireFrameUseCaseModel(
                    stateModel.Category,
                    MasterDataId.Empty,
                    MasterDataId.Empty,
                    QuestOpenStatus.NotOpenQuest);
            }
            else
            {
                return new SceneWireFrameUseCaseModel(
                    stateModel.Category,
                    sysPvpSeasonModel.Id.ToMasterDataId(),
                    MasterDataId.Empty,
                    QuestOpenStatus.Released);
            }
        }

        SceneWireFrameUseCaseModel CreateUseCaseModelAtAdventBattle(ResumableStateModel stateModel)
        {
            var mstAdventBattleModel =
                MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(stateModel.MstId);

            // 対象の降臨バトルが開催期間外だったらEmptyを返す
            bool isTimeRangeValid = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                mstAdventBattleModel.StartDateTime.Value,
                mstAdventBattleModel.EndDateTime.Value);
            if (mstAdventBattleModel.IsEmpty() || !isTimeRangeValid)
            {
                return SceneWireFrameUseCaseModel.Empty;
            }

            return new SceneWireFrameUseCaseModel(
                stateModel.Category,
                mstAdventBattleModel.Id,
                stateModel.MstEventId,
                QuestOpenStatus.Released);
        }
    }
}
