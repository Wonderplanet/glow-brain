using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestTop.Presentation.ViewModels
{
    public record EventQuestTopViewModel(
        MasterDataId MstEventId,
        MasterDataId MstQuestGroupId,
        EventName EventName,
        QuestName QuestName,
        QuestName QuestCategoryName,
        IReadOnlyList<EventQuestTopUnitViewModel> Units,
        RemainingTimeSpan RemainingAt,
        MasterDataId InitialSelectStageMstStageId,
        IReadOnlyList<EventQuestTopElementViewModel> Stages,
        ShowStageReleaseAnimation ShowStageReleaseAnimation,
        ArtworkFragmentNum GettableArtworkFragmentNum,
        ArtworkFragmentNum AcquiredArtworkFragmentNum,
        IReadOnlyList<CampaignViewModel> CampaignViewModels,
        IReadOnlyList<QuestName> NewReleaseQuestNames
        )
    {
        public string RemainingTimeText => TimeSpanFormatter.FormatUntilEnd(RemainingAt);
    };
}
