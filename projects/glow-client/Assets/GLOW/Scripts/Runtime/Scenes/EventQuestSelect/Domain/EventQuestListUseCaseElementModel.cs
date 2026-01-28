using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public record EventQuestListUseCaseElementModel(
        MasterDataId MstQuestGroupId,
        NewQuestFlag IsNewQuest,
        IReadOnlyList<QuestOpenStatus> QuestOpenStatuses,
        QuestName Name,
        EventQuestSelectElementAssetPath AssetPath,
        EventQuestUnlockRequirementDescriptionStatus RequiredStatus
    )
    {
        public static EventQuestListUseCaseElementModel Empty { get; } = new EventQuestListUseCaseElementModel(
            MasterDataId.Empty,
            NewQuestFlag.False,
            new List<QuestOpenStatus>() { QuestOpenStatus.NotOpenQuest },
            QuestName.Empty,
            EventQuestSelectElementAssetPath.Empty,
            EventQuestUnlockRequirementDescriptionStatus.Empty
        );

    };
}
