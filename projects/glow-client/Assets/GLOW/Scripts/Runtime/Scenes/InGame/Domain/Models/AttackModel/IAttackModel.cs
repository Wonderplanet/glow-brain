using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public interface IAttackModel
    {
        AttackId Id { get; }
        FieldObjectId AttackerId { get; }
        StateEffectSourceId AttackerStateEffectSourceId { get; }
        AttackViewId ViewId { get; }
        CharacterUnitRoleType AttackerRoleType { get; }
        CharacterColor AttackerColor { get; }
        IReadOnlyList<CharacterColor> KillerColors { get; }
        KillerPercentage KillerPercentage { get; }
        AttackPower BasePower { get; }
        HealPower HealPower { get; }
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus { get; }
        IReadOnlyList<PercentageM> BuffPercentages { get; }
        IReadOnlyList<PercentageM> DebuffPercentages { get; }

        bool IsEnd { get; }
        bool IsEmpty();

        (IAttackModel, IReadOnlyList<IAttackResultModel>) UpdateAttackModel(AttackModelContext context);
    }
}
