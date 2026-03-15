using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Presentation.ViewModels
{
    public record CharacterIconViewModel(
        CharacterIconAssetPath IconAssetPath,
        CharacterUnitRoleType Role,
        CharacterColor Color,
        Rarity Rarity,
        UnitLevel Level,
        BattlePoint SummonCost,
        UnitGrade Grade,
        HP Hp,
        AttackPower AttackPower,
        CharacterAttackRangeType AttackRangeType,
        UnitMoveSpeed MoveSpeed)
    {
        public string GetHpText()
        {
            return GetDefaultTextOrDashIfSpecialRole(() => Hp.ToString());
        }

        public string GetAttackPowerText()
        {
            return GetDefaultTextOrDashIfSpecialRole(() => AttackPower.ToStringN0());
        }

        public string GetAttackRangeText()
        {
            return GetDefaultTextOrDashIfSpecialRole(() => AttackRangeType.ToLocalizeString());
        }

        public string GetMoveSpeedText()
        {
            return GetDefaultTextOrDashIfSpecialRole(() => MoveSpeed.ToConvertedString());
        }

        string GetDefaultTextOrDashIfSpecialRole(Func<string> getValue)
        {
            if (Role == CharacterUnitRoleType.Special) return "-";

            return getValue();
        }
    }
}

