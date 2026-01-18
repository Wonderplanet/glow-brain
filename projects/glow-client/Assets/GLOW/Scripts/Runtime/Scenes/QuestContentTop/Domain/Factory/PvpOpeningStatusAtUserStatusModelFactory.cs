using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public class PvpOpeningStatusAtUserStatusModelFactory : IPvpQuestContentOpeningStatusModelFactory
    {
        [Inject] IMstTutorialRepository MstTutorialRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public QuestContentOpeningStatusModel Create()
        {
            var seasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            var userPvpStatusModel = GameRepository.GetGameFetchOther().UserPvpStatusModel;

            return CreateQuestContentOpeningStatusModel(
                userPvpStatusModel.RemainingChallengeCount,
                userPvpStatusModel.RemainingItemChallengeCount,
                seasonModel);
        }


        QuestContentOpeningStatusModel CreateQuestContentOpeningStatusModel(
            PvpDailyChallengeCount remainingChallengeCount,
            PvpDailyChallengeCount remainingItemChallengeCount,
            SysPvpSeasonModel sysPvpSeasonModel)
        {
            var openingStatus = GetOpeningStatus(sysPvpSeasonModel);

            var userStatus =  CreateQuestContentOpeningStatusAtUserStatus(
                remainingChallengeCount,
                remainingItemChallengeCount);

            var releaseRequiredSentence = CreateQuestContentReleaseRequiredSentence();

            return new QuestContentOpeningStatusModel(
                openingStatus,
                userStatus,
                releaseRequiredSentence);
        }

        QuestContentOpeningStatusAtTimeType GetOpeningStatus(SysPvpSeasonModel sysPvpSeasonModel)
        {
            if (sysPvpSeasonModel.IsEmpty())
            {
                return QuestContentOpeningStatusAtTimeType.BeforeOpen;
            }

            var isOpen = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                sysPvpSeasonModel.StartAt.Value,
                sysPvpSeasonModel.EndAt.Value);

            if (isOpen)
            {
                return QuestContentOpeningStatusAtTimeType.Opening;
            }

            // 集計中のときは、EndAtを過ぎて現在時刻がClosedAtに到達していないかどうかで判断
            if (CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    sysPvpSeasonModel.EndAt.Value,
                    sysPvpSeasonModel.ClosedAt.Value))
            {
                return QuestContentOpeningStatusAtTimeType.Totalizing;
            }

            return QuestContentOpeningStatusAtTimeType.OutOfLimit;
        }

        QuestContentOpeningStatusAtUserStatus CreateQuestContentOpeningStatusAtUserStatus(
            PvpDailyChallengeCount remainingChallengeCount,
            PvpDailyChallengeCount remainingItemChallengeCount)
        {
            if (!IsReleaseClearStage())
            {
                return QuestContentOpeningStatusAtUserStatus.StageLocked;
            }
            if (!remainingChallengeCount.IsEnough() && !remainingItemChallengeCount.IsEnough())
            {
                return QuestContentOpeningStatusAtUserStatus.OverLimitChallengeCount;
            }

            return QuestContentOpeningStatusAtUserStatus.None;
        }

        bool IsReleaseClearStage()
        {
            var releaseRequiredClearStageId = GetReleaseRequiredClearStageId();
            var stageModels = GameRepository.GetGameFetch().StageModels;
            var model = stageModels.FirstOrDefault(m => m.MstStageId == releaseRequiredClearStageId, StageModel.Empty);

            // ステージが存在し、クリアしているか
            return !model.IsEmpty() && model.ClearCount >= 1;
        }

        MasterDataId GetReleaseRequiredClearStageId()
        {
            var pvpTutorialModel = MstTutorialRepository.GetMstTutorialModels()
                .FirstOrDefault(m => m.TutorialFunctionName == TutorialFreePartIdDefinitions.ReleasePvp,
                    MstTutorialModel.Empty);

            if (pvpTutorialModel.IsEmpty())
            {
                return MasterDataId.Empty;
            }

            return pvpTutorialModel.ConditionValue.ToMasterDataId();
        }

        QuestContentReleaseRequiredSentence CreateQuestContentReleaseRequiredSentence()
        {
            // 開放に必要なクエストの情報
            var releaseRequiredClearStageId = GetReleaseRequiredClearStageId();
            var difficultyReleaseRequiredMstStage = MstStageDataRepository.GetMstStage(releaseRequiredClearStageId);

            if (!difficultyReleaseRequiredMstStage.IsEmpty())
            {
                var difficultyReleaseRequiredMstQuest =
                    MstQuestDataRepository.GetMstQuestModels()
                        .First(mstQuest => mstQuest.Id == difficultyReleaseRequiredMstStage.MstQuestId);
                return QuestContentReleaseRequiredSentenceFactory.Create(
                    difficultyReleaseRequiredMstQuest.Name,
                    difficultyReleaseRequiredMstQuest.Difficulty,
                    difficultyReleaseRequiredMstStage.StageNumber);
            }
            else
            {
                return QuestContentReleaseRequiredSentence.Empty;
            }
        }
    }
}
