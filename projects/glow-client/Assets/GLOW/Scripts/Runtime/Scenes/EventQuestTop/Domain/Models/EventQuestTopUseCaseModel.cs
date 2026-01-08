using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestTop.Domain.Models
{
    public record EventQuestTopUseCaseModel(
        MasterDataId MstEventId,
        MasterDataId MstQuestGroupId,
        EventName EventName,
        QuestName QuestName,
        QuestName QuestCategoryName,
        IReadOnlyList<EventQuestTopUnitUseCaseModel> Units,
        RemainingTimeSpan RemainingTime,
        DateTimeOffset QuestEndAt,
        MasterDataId InitialSelectStageMstStageId,
        IReadOnlyList<EventQuestTopElementModel> Stages,
        ShowStageReleaseAnimation ShowStageReleaseAnimation,
        ArtworkFragmentNum GettableArtworkFragmentNum,
        ArtworkFragmentNum AcquiredArtworkFragmentNum,
        IReadOnlyList<CampaignModel> CampaignModels,
        IReadOnlyList<QuestName> NewReleaseQuestNames)
    {
        public static EventQuestTopUseCaseModel Empty { get; } = new EventQuestTopUseCaseModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            EventName.Empty,
            QuestName.Empty,
            QuestName.Empty,
            new List<EventQuestTopUnitUseCaseModel>(),
            RemainingTimeSpan.Empty,
            DateTimeOffset.MinValue,
            MasterDataId.Empty,
            new List<EventQuestTopElementModel>(),
            ShowStageReleaseAnimation.Empty,
            ArtworkFragmentNum.Empty,
            ArtworkFragmentNum.Empty,
            new List<CampaignModel>(),
            new List<QuestName>()
        );
    };
}
