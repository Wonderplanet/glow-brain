using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public record EventQuestUnlockRequirementDescriptionStatus(
        RemainingTimeSpan RemainingTimeSpan,//開始までの時間
        IReadOnlyList<QuestOpenStatus> OpenStatuses,
        QuestName ReleaseRequiredQuestName,
        StageNumber ReleaseRequiredStageNumber)
    {
        public static EventQuestUnlockRequirementDescriptionStatus Empty { get; } =
            new EventQuestUnlockRequirementDescriptionStatus(
                RemainingTimeSpan.Empty,
                new List<QuestOpenStatus>(),
                QuestName.Empty,
                StageNumber.Empty
            );
    };
}
