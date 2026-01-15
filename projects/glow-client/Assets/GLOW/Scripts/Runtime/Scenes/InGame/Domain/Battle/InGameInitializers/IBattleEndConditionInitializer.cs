using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IBattleEndConditionInitializer
    {
        BattleEndModel Initialize(
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditionModels,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            InGameType inGameType,
            MstQuestModel mstQuest,
            MasterDataId mstDefenseTargetId);
    }
}
