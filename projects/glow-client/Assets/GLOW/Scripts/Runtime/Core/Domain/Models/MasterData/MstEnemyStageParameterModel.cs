using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using CharacterUnitKind = GLOW.Core.Domain.Constants.CharacterUnitKind;

namespace GLOW.Core.Domain.Models
{
    public record MstEnemyStageParameterModel(
        MasterDataId Id,
        MasterDataId MstEnemyCharacterId,
        CharacterName Name,
        CharacterUnitKind Kind,
        CharacterUnitRoleType RoleType,
        CharacterColor Color,
        MasterDataId MstSeriesId,
        UnitAssetKey AssetKey,
        SortOrder SortOrder,
        HP Hp,
        KnockBackCount DamageKnockBackCount,
        UnitMoveSpeed UnitMoveSpeed,
        WellDistance WellDistance,
        AttackPower AttackPower,
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus,
        CharacterColorAdvantageDefenseBonus ColorAdvantageDefenseBonus,
        AttackData NormalAttack,
        AttackData SpecialAttack,
        AttackData AppearanceAttack,
        AttackComboCycle AttackComboCycle,
        UnitAbility Ability,
        DropBattlePoint DropBattlePoint,
        UnitTransformationParameter TransformationParameter)
    {
        public static MstEnemyStageParameterModel Empty { get; } = new MstEnemyStageParameterModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            CharacterName.Empty,
            CharacterUnitKind.Normal,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            MasterDataId.Empty,
            UnitAssetKey.Empty,
            SortOrder.MaxValue,
            HP.Empty,
            KnockBackCount.Empty,
            UnitMoveSpeed.Empty,
            WellDistance.Empty,
            AttackPower.Empty,
            CharacterColorAdvantageAttackBonus.Empty,
            CharacterColorAdvantageDefenseBonus.Empty,
            AttackData.Empty,
            AttackData.Empty,
            AttackData.Empty,
            new AttackComboCycle(1),
            UnitAbility.Empty,
            DropBattlePoint.Empty,
            UnitTransformationParameter.Empty);

        public bool IsBoss => Kind is CharacterUnitKind.Boss or CharacterUnitKind.AdventBattleBoss;
        public bool IsNormal => Kind == CharacterUnitKind.Normal;
    }
}
