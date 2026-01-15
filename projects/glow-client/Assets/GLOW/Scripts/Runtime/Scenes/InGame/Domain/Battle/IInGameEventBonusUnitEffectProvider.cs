using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IInGameEventBonusUnitEffectProvider
    {
        EventBonusPercentage GetUnitEventBonus(MasterDataId mstUnitId, MasterDataId mstQuestId);
        EventBonusPercentage GetUnitEventBonus(MasterDataId mstUnitId, EventBonusGroupId eventBonusGroupId);
        PercentageM GetUnitEventBonusPercentageM(MasterDataId mstUnitId, MasterDataId mstQuestId);
        PercentageM GetUnitEventBonusPercentageM(MasterDataId mstUnitId, EventBonusGroupId eventBonusGroupId);
    }
}
