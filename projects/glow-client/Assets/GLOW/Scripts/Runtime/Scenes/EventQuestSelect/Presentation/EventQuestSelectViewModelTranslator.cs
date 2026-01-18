using System;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using GLOW.Scenes.QuestSelect.Presentation;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public static class EventQuestSelectViewModelTranslator
    {
        public static EventQuestSelectViewModel Translate(EventQuestListUseCaseModel model)
        {
            var elements = model.Quests.Select(TranslateItem).ToList();
            return new EventQuestSelectViewModel(
                model.MstEventId,
                model.AdventBattleModel.MstAdventBattleId,
                model.AdventBattleModel.AdventBattleOpenStatus,
                CreateAdventBattleOpenSentence(
                    model.AdventBattleModel.RequiredUserLevel,
                    model.AdventBattleModel.AdventBattleOpenStatus),
                CreateAdventBattleRemainTimeSentence(
                    model.AdventBattleModel.AdventBattleTimeSpan.Value,
                    model.AdventBattleModel.AdventBattleOpenStatus),
                model.AdventBattleModel.AdventBattleName,
                model.EventAssetKey,
                model.RemainingTime,
                model.EventEndAt,
                elements,
                model.RemainingEventCampaignTimeSpan,
                model.MstBoxGachaId,
                model.IsBoxGachaDrawable);
        }

        static AdventBattleOpenSentence CreateAdventBattleOpenSentence(UserLevel userLevel, AdventBattleOpenStatus status)
        {
            return status.Value switch
            {
                AdventBattleOpenStatusType.RankLocked =>
                    new AdventBattleOpenSentence(ZString.Format("リーダーLv. {0}で開放", userLevel.Value)),
                _ => new AdventBattleOpenSentence(string.Empty)
            };
        }

        static AdventBattleRemainTimeSentence CreateAdventBattleRemainTimeSentence(
            TimeSpan timeSpan,
            AdventBattleOpenStatus status)
        {
            var timeSpanString = TimeSpanFormatter.FormatRemaining(timeSpan);
            return status.Value switch
            {
                AdventBattleOpenStatusType.BeforeOpened => new AdventBattleRemainTimeSentence("開催まで：", timeSpanString),
                AdventBattleOpenStatusType.Opened => new AdventBattleRemainTimeSentence("終了まで：", timeSpanString),
                // AdventBattleOpenStatusType.RankLocked => new AdventBattleOpenSentence(ZString.Format("リーダーLv. {0}で開放")),
                _ => new AdventBattleRemainTimeSentence(string.Empty, string.Empty)
            };
        }

        static EventQuestSelectElementViewModel TranslateItem(EventQuestListUseCaseElementModel model)
        {
            return new EventQuestSelectElementViewModel(
                model.MstQuestGroupId,
                model.QuestOpenStatuses,
                model.IsNewQuest,
                model.Name,
                model.AssetPath,
                CreateRequirementDescription(model.RequiredStatus)
            );
        }


        static QuestUnlockRequirementDescription CreateRequirementDescription(
            EventQuestUnlockRequirementDescriptionStatus status
        )
        {
            if (status.OpenStatuses.Exists(s => s == QuestOpenStatus.QuestEnded))
            {
                // 終了時は、終了時テキストだけにする
                return QuestUnlockRequirementDescription.CreateQuestEndedSentence();
            }

            if (status.OpenStatuses.Exists(s => s == QuestOpenStatus.NotOpenQuest))
            {
                // 未開催のときは、未開催テキストだけにする
                return QuestUnlockRequirementDescription.CreateOpenLimitSentenceAtEvent(status.RemainingTimeSpan.Value);
            }

            var descriptions = status.OpenStatuses
                .Select(s =>
                    s.ToDescription(
                        status.ReleaseRequiredQuestName,
                        status.ReleaseRequiredStageNumber,
                        status.RemainingTimeSpan))
                .Where(d => !d.IsEmpty())
                .ToList();

            // 何も無ければEmpty返す
            if (!descriptions.Any()) return QuestUnlockRequirementDescription.Empty;
            // 1つだけあればそれを返す
            if (descriptions.Count() == 1) return descriptions.First();

            //改行しつつValueを合成
            var mergedSentence = descriptions
                .Select(d => d.Value)
                .Aggregate((a, b) => a + "\n" + b);
            return new QuestUnlockRequirementDescription(mergedSentence);
        }

        static QuestUnlockRequirementDescription ToDescription(
            this QuestOpenStatus openStatus,
            QuestName questName,
            StageNumber stageNumber,
            RemainingTimeSpan remainingTimeSpan)
        {
            return openStatus switch
            {
                QuestOpenStatus.Released => QuestUnlockRequirementDescription.Empty,
                QuestOpenStatus.NoClearRequiredStage =>
                    QuestUnlockRequirementDescription.CreateNoClearRequiredStageSentence(questName, stageNumber),
                QuestOpenStatus.NotOpenQuest =>
                    QuestUnlockRequirementDescription.CreateOpenLimitSentenceAtEvent(remainingTimeSpan.Value),
                QuestOpenStatus.QuestEnded =>
                    QuestUnlockRequirementDescription.CreateQuestEndedSentence(),
                QuestOpenStatus.NoPlayableStage => QuestUnlockRequirementDescription.CreateNoStagePlayableSentence(),
                _ => QuestUnlockRequirementDescription.Empty
            };
        }
    }
}
