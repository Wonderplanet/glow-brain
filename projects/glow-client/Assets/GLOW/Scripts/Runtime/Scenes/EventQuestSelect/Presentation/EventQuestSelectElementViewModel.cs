using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.QuestSelect.Presentation;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public record EventQuestSelectElementViewModel(
        MasterDataId MstQuestGroupId,
        IReadOnlyList<QuestOpenStatus> OpenStatuses,
        NewQuestFlag IsNewQuest,
        QuestName Name,
        EventQuestSelectElementAssetPath AssetPath,
        QuestUnlockRequirementDescription UnlockRequirementDescription
    )
    {
        static string QuestEndedText = "開催期間が終了しました";
        static string OverLimitChallengeableText = "本日の挑戦回数が終了しました";

        public bool IsOpen()
        {
            return OpenStatuses.Exists(s => s == QuestOpenStatus.Released);
        }

        public bool ShouldShowLockIcon()
        {
            // 細かくifで区切っているのは表示優先度の関係
            if (OpenStatuses.Exists(s => s == QuestOpenStatus.QuestEnded))
            {
                return false;
            }

            if (OpenStatuses.Exists(s => s == QuestOpenStatus.NotOpenQuest))
            {
                return true;
            }

            if (OpenStatuses.Exists(s => s == QuestOpenStatus.NoPlayableStage))
            {
                return false;
            }

            return OpenStatuses
                .Exists(s => s == QuestOpenStatus.NoClearRequiredStage);
        }

        public string GetLockDescription()
        {
            // 細かくifで区切っているのは表示優先度の関係
            if (OpenStatuses.Exists(s => s == QuestOpenStatus.NotOpenQuest) &&
                OpenStatuses.All(s => s != QuestOpenStatus.QuestEnded))
            {
                return string.Empty;
            }

            if (OpenStatuses
                .Exists(s => s == QuestOpenStatus.QuestEnded))
            {
                return QuestEndedText;
            }

            if (OpenStatuses
                .Exists(s => s == QuestOpenStatus.NoPlayableStage))
            {
                return OverLimitChallengeableText;
            }

            return String.Empty;
        }
    };
}
