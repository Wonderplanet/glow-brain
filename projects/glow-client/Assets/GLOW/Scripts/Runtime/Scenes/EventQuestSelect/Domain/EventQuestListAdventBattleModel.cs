using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;

namespace GLOW.Scenes.EventQuestSelect.Domain
{
    public record EventQuestListAdventBattleModel(
        MasterDataId MstAdventBattleId,
        AdventBattleOpenStatus AdventBattleOpenStatus,
        RemainingTimeSpan AdventBattleTimeSpan, //OpenStatusと2種1組。開催前後でTimeSpanが変化する
        AdventBattleName AdventBattleName,
        UserLevel RequiredUserLevel)
    {
        public static EventQuestListAdventBattleModel Empty { get; } = new(
            MasterDataId.Empty,
            AdventBattleOpenStatus.Empty,
            RemainingTimeSpan.Empty,
            AdventBattleName.Empty,
            UserLevel.Empty);

    }
}
