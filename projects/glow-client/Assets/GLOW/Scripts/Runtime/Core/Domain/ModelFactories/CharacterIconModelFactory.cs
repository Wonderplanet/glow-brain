using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using Zenject;

namespace GLOW.Core.Domain.ModelFactories
{
    public static class CharacterIconModelFactory
    {
        public static CharacterIconModel Create(MstCharacterModel mstCharacter, UserUnitModel userUnit, UnitCalculateStatusModel unitCalculateStatusModel)
        {
            return new CharacterIconModel(
                CharacterIconAssetPath.FromAssetKey(mstCharacter.AssetKey),
                mstCharacter.RoleType,
                mstCharacter.Color,
                mstCharacter.Rarity,
                userUnit.Level,
                mstCharacter.SummonCost,
                userUnit.Grade,
                unitCalculateStatusModel.HP,
                unitCalculateStatusModel.AttackPower,
                mstCharacter.AttackRangeType,
                mstCharacter.UnitMoveSpeed
            );
        }

        public static CharacterIconModel CreateLSize(MstCharacterModel mstCharacter, UserUnitModel userUnit, UnitCalculateStatusModel unitCalculateStatusModel)
        {
            return new CharacterIconModel(
                CharacterIconAssetPath.FromAssetKeyForLSize(mstCharacter.AssetKey),
                mstCharacter.RoleType,
                mstCharacter.Color,
                mstCharacter.Rarity,
                userUnit.Level,
                mstCharacter.SummonCost,
                userUnit.Grade,
                unitCalculateStatusModel.HP,
                unitCalculateStatusModel.AttackPower,
                mstCharacter.AttackRangeType,
                mstCharacter.UnitMoveSpeed
            );
        }

        public static CharacterIconModel Create(MstCharacterModel mstCharacter)
        {
            return new CharacterIconModel(
                CharacterIconAssetPath.FromAssetKey(mstCharacter.AssetKey),
                mstCharacter.RoleType,
                mstCharacter.Color,
                mstCharacter.Rarity,
                UnitLevel.Empty,
                mstCharacter.SummonCost,
                UnitGrade.Empty,
                HP.Empty,
                AttackPower.Empty,
                mstCharacter.AttackRangeType,
                mstCharacter.UnitMoveSpeed
            );
        }

        public static CharacterIconModel Create(MstEnemyStageParameterModel mstEnemy)
        {
            return new CharacterIconModel(
                CharacterIconAssetPath.FromAssetKey(mstEnemy.AssetKey),
                mstEnemy.RoleType,
                mstEnemy.Color,
                Rarity.R,
                UnitLevel.Empty,
                BattlePoint.Empty,
                UnitGrade.Empty,
                HP.Empty,
                AttackPower.Empty,
                CharacterAttackRangeType.Short,
                UnitMoveSpeed.Empty
            );
        }
    }
}
