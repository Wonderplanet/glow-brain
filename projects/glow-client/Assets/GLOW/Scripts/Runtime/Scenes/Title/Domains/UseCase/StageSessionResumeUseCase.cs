using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public class StageSessionResumeUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }

        public StageSessionResumeUseCaseModel GetModel(InGameContentType inGameContentType, MasterDataId mstId)
        {
            return inGameContentType switch
            {
                InGameContentType.AdventBattle => CreateStageSessionResumeUseCaseModelAtAdventBattle(mstId),
                InGameContentType.Pvp => CreateStageSessionResumeUseCaseModelAtPvp(mstId),
                InGameContentType.Stage => CreateStageSessionResumeUseCaseModelAtStage(mstId),
                _ => CreateStageSessionResumeUseCaseModelAtStage(mstId),
            };
        }

        StageSessionResumeUseCaseModel CreateStageSessionResumeUseCaseModelAtAdventBattle(MasterDataId mstAdventBattleId)
        {
            var mstAdventBattle = MstAdventBattleDataRepository.GetMstAdventBattleModel(mstAdventBattleId);
            var isOpen = mstAdventBattle.StartDateTime <= TimeProvider.Now && TimeProvider.Now < mstAdventBattle.EndDateTime;

            return new StageSessionResumeUseCaseModel(
                new StageSessionOpenFlag(isOpen),
                new SessionAbortConfirmAttentionText(SessionResumeConst.AdventBattleAbortConfirmAttentionText));
        }

        StageSessionResumeUseCaseModel CreateStageSessionResumeUseCaseModelAtPvp(MasterDataId sysPvpSeasonId)
        {
            var currentSysPvpSeason = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            if(currentSysPvpSeason.Id != sysPvpSeasonId)
            {
                return new StageSessionResumeUseCaseModel(
                    StageSessionOpenFlag.False, //sysIdが現在のものではない
                    new SessionAbortConfirmAttentionText(SessionResumeConst.PvpAbortConfirmAttentionText));
            }

            var isOpen = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                currentSysPvpSeason.StartAt.Value,
                currentSysPvpSeason.EndAt.Value
                );

            return new StageSessionResumeUseCaseModel(
                new StageSessionOpenFlag(isOpen),
                new SessionAbortConfirmAttentionText(SessionResumeConst.PvpAbortConfirmAttentionText));
        }

        StageSessionResumeUseCaseModel CreateStageSessionResumeUseCaseModelAtStage(MasterDataId mstId)
        {
            var mstStage = MstStageDataRepository.GetMstStage(mstId);
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);

            switch (mstQuest.QuestType)
            {
                case QuestType.Event:
                {
                    // イベントのときは開催チェックする
                    var mstEvent = MstEventDataRepository.GetEvent(mstQuest.MstEventId);
                    var isOpen = mstEvent.StartAt <= TimeProvider.Now && TimeProvider.Now < mstEvent.EndAt;
                    return new StageSessionResumeUseCaseModel(
                        new StageSessionOpenFlag(isOpen),
                        new SessionAbortConfirmAttentionText(SessionResumeConst.StageAbortConfirmAttentionText));
                }
                case QuestType.Enhance:
                    // Enhanceのときは中断復帰破棄の文言を変える
                    return new StageSessionResumeUseCaseModel(
                        StageSessionOpenFlag.True,
                        new SessionAbortConfirmAttentionText(SessionResumeConst.EnhanceAbortConfirmAttentionText));
                default:
                    return new StageSessionResumeUseCaseModel(
                        StageSessionOpenFlag.True,
                        new SessionAbortConfirmAttentionText(SessionResumeConst.StageAbortConfirmAttentionText));
            }
        }
    }
}
