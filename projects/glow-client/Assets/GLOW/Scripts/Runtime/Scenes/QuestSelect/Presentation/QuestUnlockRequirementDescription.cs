using System;
using Cysharp.Text;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Modules;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public record QuestUnlockRequirementDescription(ObscuredString Value)
    {
        public static QuestUnlockRequirementDescription Empty { get; } = new QuestUnlockRequirementDescription(string.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public static QuestUnlockRequirementDescription CreateRequiredSentence(string value)
        {
            return new QuestUnlockRequirementDescription($"{value}\nクリアで開放");
        }

        public static QuestUnlockRequirementDescription CreateOpenLimitSentence(TimeSpan limitTimeSpan)
        {
            return new QuestUnlockRequirementDescription(TimeSpanFormatter.FormatUntilReleaseWithLB(limitTimeSpan));
        }

        public static QuestUnlockRequirementDescription CreateOpenLimitSentenceAtEvent(TimeSpan limitTimeSpan)
        {
            return new QuestUnlockRequirementDescription(TimeSpanFormatter.FormatUntilRelease(limitTimeSpan));
        }

        public static QuestUnlockRequirementDescription CreateNoStagePlayableSentence()
        {
            return new QuestUnlockRequirementDescription("本日の挑戦回数が終了しました");
        }

        public static QuestUnlockRequirementDescription CreateEmptySentence(string value)
        {
            return new QuestUnlockRequirementDescription(value);
        }

        public static QuestUnlockRequirementDescription CreateNoClearRequiredStageSentence(QuestName questName, StageNumber stageNumber)
        {
            return new QuestUnlockRequirementDescription(ZString.Format("{0} {1}話クリアで開放", questName.Value, stageNumber.Value));
        }

        public static QuestUnlockRequirementDescription CreateQuestEndedSentence()
        {
            return new QuestUnlockRequirementDescription("開催期間が終了しました");
        }
    };
}
