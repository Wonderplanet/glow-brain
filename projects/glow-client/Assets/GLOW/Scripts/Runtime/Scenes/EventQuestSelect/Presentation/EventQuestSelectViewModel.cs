using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    public record EventQuestSelectViewModel(
        MasterDataId MstEventId,
        MasterDataId MstAdventBattleId,
        AdventBattleOpenStatus AdventBattleOpenStatus,
        AdventBattleOpenSentence AdventBattleOpenSentence,
        AdventBattleRemainTimeSentence AdventBattleRemainTimeSentence,
        AdventBattleName AdventBattleName,
        EventAssetKey EventAssetKey,
        RemainingTimeSpan RemainingAt,
        DateTimeOffset EventEndAt,
        IReadOnlyList<EventQuestSelectElementViewModel> Quests,
        RemainingTimeSpan RemainingEventCampaignTimeSpan,
        MasterDataId MstBoxGachaId,
        BoxGachaDrawableFlag IsBoxGachaDrawable)
    {
        public string RemainingAtText => TimeSpanFormatter.FormatUntilEnd(RemainingAt);
        public bool IsOpen => !RemainingAt.IsMinus();
    };
}
