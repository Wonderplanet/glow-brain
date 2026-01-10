using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record UserInGameStatusModel(
        InGameSessionStartedFlag IsStartedSession,
        InGameContentType InGameContentType,
        MasterDataId TargetMstId,
        PartyNo PartyNo,
        ContinueCount ContinueCount,
        ContinueCount ContinueAdCount)
    {
        public static UserInGameStatusModel Empty { get; } = new UserInGameStatusModel(
            InGameSessionStartedFlag.False,
            InGameContentType.Stage,
            MasterDataId.Empty,
            PartyNo.One,
            ContinueCount.Zero,
            ContinueCount.Zero
        );

        // InGameContentTypeをInGameTypeに変換
        public InGameType InGameType =>
            InGameContentType switch
            {
                InGameContentType.Stage => InGameType.Normal,
                InGameContentType.AdventBattle => InGameType.AdventBattle,
                InGameContentType.Pvp => InGameType.Pvp,
                _ => throw new System.ArgumentOutOfRangeException(nameof(InGameContentType), InGameContentType, null)
            };
    };
}
