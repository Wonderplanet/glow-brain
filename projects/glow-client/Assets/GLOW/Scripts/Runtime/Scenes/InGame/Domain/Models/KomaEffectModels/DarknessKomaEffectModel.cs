using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record DarknessKomaEffectModel(
            KomaId KomaId,
            KomaEffectType EffectType,
            KomaEffectTargetSide TargetSide,
            IReadOnlyList<CharacterColor> TargetColors,
            IReadOnlyList<CharacterUnitRoleType> TargetRoles,
            DarknessClearedFlag Cleared)
        : BaseKomaEffectModel(
            KomaId,
            EffectType,
            TargetSide,
            TargetColors,
            TargetRoles)
    {
        public static DarknessKomaEffectModel Empty { get; } = new(
            KomaId.Empty,
            KomaEffectType.None,
            KomaEffectTargetSide.All,
            new List<CharacterColor>(),
            new List<CharacterUnitRoleType>(),
            DarknessClearedFlag.False);

        public override bool IsEffective()
        {
            return !Cleared;
        }

        public override bool CanSelectAsOutpostWeaponTarget()
        {
            return Cleared;
        }

        public override bool CanSelectAsSpecialUnitSummonTarget()
        {
            return Cleared;
        }

        public override IKomaEffectModel GetUpdatedModel(KomaEffectUpdateContext context)
        {
            if (Cleared) return this;

            var targetIsInside = context.Units.Any(unit => unit.LocatedKoma.Id == KomaId && IsTarget(unit));

            if (targetIsInside)
            {
                return this with { Cleared = DarknessClearedFlag.True };
            }

            return this;
        }

        public override IKomaEffectModel GetResetModel()
        {
            return this with
            {
                Cleared = DarknessClearedFlag.False
            };
        }
    }
}
