using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IInGameUnitStatusCalculator
    {
        AttackPower CalculateBuffAttackPower(AttackPower attackPower, IReadOnlyList<IStateEffectModel> buffs);
        UnitMoveSpeed CalculateBuffUnitMoveSpeed(UnitMoveSpeed moveSpeed, IReadOnlyList<IStateEffectModel> buffs);
        UnitCalculateStatusModel CalculateStatus(
            UnitCalculateStatusModel calculatedStatus,
            MasterDataId unitId,
            IInGameUnitEncyclopediaEffectProvider unitEncyclopediaEffectProvider,
            InGameType inGameType,
            MasterDataId questId,
            EventBonusGroupId eventBonusGroupId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);

        UnitCalculateStatusModel CalculateStatusWithEncyclopediaEffect(
            UnitCalculateStatusModel calculatedStatus,
            IInGameUnitEncyclopediaEffectProvider unitEncyclopediaEffectProvider);
    }
}
