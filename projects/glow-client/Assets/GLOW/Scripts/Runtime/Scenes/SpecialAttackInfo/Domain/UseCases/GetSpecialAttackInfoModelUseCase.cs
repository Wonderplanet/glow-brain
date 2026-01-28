using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.SpecialAttackInfo.Domain.UseCases
{
    public class GetSpecialAttackInfoModelUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public SpecialAttackInfoModel GetSpecialAttackInfoModel(MasterDataId unitId, UnitGrade unitGrade, UnitLevel unitLevel)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(unitId);
            return SpecialAttackInfoModelFactory.Create(mstUnit, unitGrade, unitLevel);
        }
    }
}
