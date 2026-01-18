using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.ModelFactories
{
    public interface ISpecialRoleSpecialAttackFactory
    {
        AttackData CreateSpecialRoleSpecialAttack(
            MstCharacterModel mstUnit,
            UserUnitModel userUnit);

        AttackData CreateSpecialRoleSpecialAttack(
            MstCharacterModel mstUnit,
            UnitGrade unitGrade,
            UnitLevel currentLevel);
        
        AttackData CreateSpecialRoleSpecialAttack(
            MstSpecialAttackModel mstSpecialAttack,
            UnitLevel currentLevel,
            UnitLevel maxLevel);
    }
}