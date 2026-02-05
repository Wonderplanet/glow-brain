using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationPartyMemberModel(
        UserDataId UserUnitId,
        UnitImageAssetPath ImageAssetPath,
        CharacterColor Color,
        Rarity Rarity,
        UnitLevel Level,
        BattlePoint Cost,
        UnitGrade Grade,
        HP HP,
        AttackPower AttackPower,
        CharacterUnitRoleType Role,
        CharacterAttackRangeType AttackRangeType,
        UnitMoveSpeed MoveSpeed,
        UnitListSortType SortType,
        EventBonusPercentage EventBonus,
        PartyFormationPartySpecialRuleItemModel SpecialRuleItemModel,
        InGameSpecialRuleUnitStatusTargetFlag InGameSpecialRuleUnitStatusTargetFlag
        )
    {
        public static PartyFormationPartyMemberModel Empty { get; } = new (
            UserDataId.Empty,
            UnitImageAssetPath.Empty,
            CharacterColor.None,
            Rarity.R,
            UnitLevel.Empty,
            BattlePoint.Empty,
            UnitGrade.Empty,
            HP.Empty,
            AttackPower.Empty,
            CharacterUnitRoleType.Attack,
            CharacterAttackRangeType.Short,
            UnitMoveSpeed.Empty,
            UnitListSortType.Rarity,
            EventBonusPercentage.Empty,
            PartyFormationPartySpecialRuleItemModel.Empty,
            InGameSpecialRuleUnitStatusTargetFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
