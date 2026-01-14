using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public record EventQuestListUseCaseModel(
        MasterDataId MstEventId,
        EventQuestListAdventBattleModel AdventBattleModel,
        EventAssetKey EventAssetKey,
        RemainingTimeSpan RemainingTime,
        DateTimeOffset EventEndAt,
        IReadOnlyList<EventQuestListUseCaseElementModel> Quests,
        RemainingTimeSpan RemainingEventCampaignTimeSpan,
        MasterDataId MstBoxGachaId,
        BoxGachaDrawableFlag IsBoxGachaDrawable);
}
