using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.PartyFormation.Presentation.ViewModels
{
    public record PartyFormationPartyMemberViewModel(
        UserDataId UserUnitId,
        UnitImageAssetPath ImageAssetPath,
        CharacterColor Color,
        Rarity Rarity,
        UnitLevel Level,
        BattlePoint Cost,
        UnitGrade Grade,
        HP Hp,
        AttackPower AttackPower,
        CharacterUnitRoleType Role,
        CharacterAttackRangeType AttackRangeType,
        UnitMoveSpeed MoveSpeed,
        UnitListSortType SortType,
        EventBonusPercentage EventBonus,
        InGameSpecialRuleAchievedFlag IsAchievedSpecialRule,
        InGameSpecialRuleUnitStatusTargetFlag IsInGameSpecialRuleUnitStatusTarget)
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
