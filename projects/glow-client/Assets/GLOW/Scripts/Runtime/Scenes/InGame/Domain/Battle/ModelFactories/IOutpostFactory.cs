using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IOutpostFactory
    {
        OutpostModel GenerateOutpost(
            MstArtworkModel artwork,
            OutpostAssetKey outpostAssetKey,
            QuestType questType,
            IReadOnlyList<MstInGameSpecialRuleModel> specialRules,
            OutpostEnhancementModel outpostEnhancement,
            HP artworkBonusHp,
            InGameContinueSelectingFlag isContinueSelecting);

        OutpostModel GenerateOutpost(MstEnemyOutpostModel outpost);

        OutpostModel GenerateOpponentOutpost(
            OutpostEnhancementModel outpostEnhancement,
            HP artworkBonusHp,
            MstEnemyOutpostModel outpost);
    }
}
