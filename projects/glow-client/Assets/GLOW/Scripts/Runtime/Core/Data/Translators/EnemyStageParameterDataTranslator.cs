using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class EnemyStageParameterDataTranslator
    {
        public static MstEnemyStageParameterModel ToEnemyStageParameterModel(
            MstEnemyCharacterData mstEnemyCharacterData,
            MstEnemyStageParameterData mstEnemyStageParameterData,
            MstEnemyCharacterI18nData mstEnemyCharacterI18nData,
            MstUnitAbilityData mstUnitAbilityData,
            MstAbilityI18nData mstAbilityI18nData,
            MstAbilityData mstAbilityData,
            MstUnitRoleBonusData mstUnitRoleBonusData,
            AttackData normalAttack,
            AttackData specialAttack,
            AttackData appearanceAttack)
        {
            // キャラ特性
            var ability = MstUnitAbilityModelTranslator.Translate(mstUnitAbilityData, mstAbilityData, mstAbilityI18nData, UnitRank.Empty);

            // 変身
            var transformationParameter = string.IsNullOrEmpty(mstEnemyStageParameterData.MstTransformationEnemyStageParameterId)
                ? UnitTransformationParameter.Empty
                : new UnitTransformationParameter(
                    new MasterDataId(mstEnemyStageParameterData.MstTransformationEnemyStageParameterId),
                    mstEnemyStageParameterData.TransformationConditionType,
                    new UnitTransformationConditionValue(mstEnemyStageParameterData.TransformationConditionValue));

            return new MstEnemyStageParameterModel(
                new MasterDataId(mstEnemyStageParameterData.Id),
                new MasterDataId(mstEnemyCharacterData.Id),
                new CharacterName(mstEnemyCharacterI18nData.Name),
                mstEnemyStageParameterData.CharacterUnitKind,
                mstEnemyStageParameterData.RoleType,
                mstEnemyStageParameterData.Color,
                new MasterDataId(mstEnemyCharacterData.MstSeriesId),
                new UnitAssetKey(mstEnemyCharacterData.AssetKey),
                new SortOrder(mstEnemyStageParameterData.SortOrder),
                new HP(mstEnemyStageParameterData.Hp),
                new KnockBackCount(mstEnemyStageParameterData.DamageKnockBackCount),
                new UnitMoveSpeed(mstEnemyStageParameterData.MoveSpeed),
                new WellDistance(mstEnemyStageParameterData.WellDistance),
                new AttackPower(mstEnemyStageParameterData.AttackPower),
                new CharacterColorAdvantageAttackBonus(mstUnitRoleBonusData.ColorAdvantageAttackBonus),
                new CharacterColorAdvantageDefenseBonus(mstUnitRoleBonusData.ColorAdvantageDefenseBonus),
                normalAttack,
                specialAttack,
                appearanceAttack,
                new AttackComboCycle(mstEnemyStageParameterData.AttackComboCycle),
                ability.UnitAbility,
                new DropBattlePoint(mstEnemyStageParameterData.DropBattlePoint),
                transformationParameter);
        }
    }
}
