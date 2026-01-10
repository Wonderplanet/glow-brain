using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.ModelFactories
{
    public interface ISpecialAttackInfoModelFactory
    {
        SpecialAttackInfoModel Create(MstCharacterModel mstUnit, UserUnitModel userUnit);
        SpecialAttackInfoModel Create(MstCharacterModel mstUnit, UnitGrade unitGrade, UnitLevel unitLevel);
    }
}

