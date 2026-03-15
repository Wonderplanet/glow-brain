using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public record EmptyAttackModel : IAttackModel
    {
        public AttackId Id => AttackId.Empty;
        public FieldObjectId AttackerId => FieldObjectId.Empty;
        public StateEffectSourceId AttackerStateEffectSourceId => StateEffectSourceId.Empty;
        public AttackViewId ViewId => AttackViewId.Empty;
        public CharacterUnitRoleType AttackerRoleType => CharacterUnitRoleType.None;
        public CharacterColor AttackerColor => CharacterColor.None;
        public IReadOnlyList<CharacterColor> KillerColors => Array.Empty<CharacterColor>();
        public KillerPercentage KillerPercentage => KillerPercentage.Empty;
        public AttackPower BasePower => AttackPower.Empty;
        public HealPower HealPower => HealPower.Empty;
        public CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus => CharacterColorAdvantageAttackBonus.Empty;
        public IReadOnlyList<PercentageM> BuffPercentages => Array.Empty<PercentageM>();
        public IReadOnlyList<PercentageM> DebuffPercentages => Array.Empty<PercentageM>();
        public bool IsEnd => true;

        public bool IsEmpty()
        {
            return true;
        }

        public (IAttackModel, IReadOnlyList<IAttackResultModel>) UpdateAttackModel(AttackModelContext context)
        {
            return (this, Array.Empty<IAttackResultModel>());
        }
    }
}
