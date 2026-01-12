using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IHPCalculator
    {
        HPCalculatorResultModel CalculateHp(
            IReadOnlyList<HitAttackResultModel> attackResults,
            FieldObjectId targetId,
            CharacterColor targetColor,
            CharacterColorAdvantageDefenseBonus targetColorAdvantageDefenseBonus,
            HP currentHp,
            HP maxHp,
            IReadOnlyList<IStateEffectModel> stateEffects,
            DamageInvalidationFlag isDamageInvalidation,
            HealInvalidationFlag isHealInvalidation,
            UndeadFlag isUndead);
    }
}
