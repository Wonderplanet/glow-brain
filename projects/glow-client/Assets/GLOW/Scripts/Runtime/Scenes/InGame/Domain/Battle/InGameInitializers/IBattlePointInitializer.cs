using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IBattlePointInitializer
    {
        BattlePointInitializerResult Initialize(
            InGameType type,
            MasterDataId mstAdventBattleId,
            ContentSeasonSystemId sysPvpSeasonId,
            OutpostEnhancementModel outpostEnhancement,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement,
            InGameContinueSelectingFlag isInGameContinueSelecting);
    }
}
