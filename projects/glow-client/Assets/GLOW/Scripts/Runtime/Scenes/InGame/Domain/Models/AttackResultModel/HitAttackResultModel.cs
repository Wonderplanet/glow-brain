using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackResultModel
{
    public record HitAttackResultModel(
        FieldObjectId TargetId,
        FieldObjectId AttackerId,
        AttackDamageType AttackDamageType,
        AttackHitData AttackHitData,
        AttackHitStopFlag IsHitStop,
        CharacterUnitRoleType AttackerRoleType,
        CharacterColor AttackerColor,
        IReadOnlyList<CharacterColor> KillerColors,
        KillerPercentage KillerPercentage,
        AttackPower BasePower,
        AttackPowerParameter PowerParameter,
        HealPower HealPower,
        CharacterColorAdvantageAttackBonus CharacterColorAdvantageAttackBonus,
        IReadOnlyList<PercentageM> BuffPercentages,
        IReadOnlyList<PercentageM> DebuffPercentages,
        StateEffectSourceId StateEffectSourceId,
        StateEffect StateEffect) : IAttackResultModel
    {
        public static HitAttackResultModel Empty { get; } = new(
            FieldObjectId.Empty,
            FieldObjectId.Empty,
            AttackDamageType.None,
            AttackHitData.Empty,
            AttackHitStopFlag.False,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            new List<CharacterColor>(),
            KillerPercentage.Empty,
            AttackPower.Empty,
            AttackPowerParameter.Empty,
            HealPower.Empty,
            CharacterColorAdvantageAttackBonus.Empty,
            new List<PercentageM>(),
            new List<PercentageM>(),
            StateEffectSourceId.Empty,
            StateEffect.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsAdvantageColor(CharacterColor targetColor)
        {
            return AttackerColor.IsAdvantage(targetColor);
        }

        public bool IsDisAdvantageColor(CharacterColor targetColor)
        {
            return targetColor.IsAdvantage(AttackerColor);
        }

        public bool IsKiller(CharacterColor color)
        {
            return color != CharacterColor.None && KillerColors.Contains(color);
        }
    }
}
