using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EventQuestTop.Presentation.ValueObjects;

namespace GLOW.Scenes.EventStageSelect.Presentation.ViewModels
{
    public record EventStageSelectElementViewModel(
        MasterDataId MstStageId,
        EventQuestTopNewFlag IsNew,
        EventQuestTopClearedFlag IsCleared,
        StagePlayableFlag StagePlayableFlag,
        StageClearCount DailyClearCount,
        ClearableCount DailyPlayableCount,
        StageConsumeStamina ConsumedStamina,
        StageReleaseRequireSentence ReleaseRequireSentence,
        SortOrder SortOrder,
        UnlimitedCalculableDateTimeOffset StageEndAt,
        EventQuestTopShouldShowReleaseAnimationFlag ShouldShowReleaseAnimationFlag,
        InGameSpecialRuleAchievedFlag IsAchieveInGameSpecialRule);
}
