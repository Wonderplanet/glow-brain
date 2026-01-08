using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IOutpostInitializer
    {
        OutpostInitializerResult Initialize(
            InGameType inGameType,
            QuestType questType,
            OutpostAssetKey outpostAssetKey,
            MasterDataId mstEnemyOutpostId,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            OutpostEnhancementModel outpostEnhancement,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement,
            HP artworkBonusHp,
            HP pvpOpponentArtworkBonusHp,
            InGameContinueSelectingFlag isContinueSelecting);
    }
}
