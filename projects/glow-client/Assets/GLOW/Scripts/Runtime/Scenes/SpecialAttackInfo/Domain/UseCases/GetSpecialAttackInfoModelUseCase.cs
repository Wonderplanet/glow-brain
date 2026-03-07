using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.SpecialAttackInfo.Domain.Model;
using Zenject;

namespace GLOW.Scenes.SpecialAttackInfo.Domain.UseCases
{
    public class GetSpecialAttackInfoModelUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }
        [Inject] IMstUnitGradeUpRewardRepository MstUnitGradeUpRewardRepository { get; }

        public SpecialAttackInfoUseCaseModel GetSpecialAttackInfoModel(MasterDataId unitId, UnitGrade unitGrade, UnitLevel unitLevel)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(unitId);
            var infoModel = SpecialAttackInfoModelFactory.Create(mstUnit, unitGrade, unitLevel);
            var gradeUpReward = MstUnitGradeUpRewardRepository.GetUnitGradeUpRewardFirstOrDefault(mstUnit.Id);

            var unitName = CharacterName.Empty;
            if (!gradeUpReward.IsEmpty() && gradeUpReward.ResourceType == ResourceType.Artwork)
            {
                unitName = mstUnit.Name;
            }

            return new SpecialAttackInfoUseCaseModel(
                infoModel,
                unitName);
        }
    }
}
