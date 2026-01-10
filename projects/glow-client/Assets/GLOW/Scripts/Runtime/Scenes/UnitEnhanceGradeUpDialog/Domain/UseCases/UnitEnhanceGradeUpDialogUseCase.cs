using System.Linq;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.Models;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpDialog.Domain.UseCases
{
    public class UnitEnhanceGradeUpDialogUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] ISpecialAttackDescriptionEncoder SpecialAttackDescriptionEncoder { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public UnitEnhanceGradeUpDialogModel GetUnitEnhanceGradeUpDialogModel(UserDataId userUnitId, UnitGrade beforeGrade, UnitGrade afterGrade)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            var beforeStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, beforeGrade);
            var afterStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, afterGrade);

            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(mstUnit, afterGrade, userUnit.Level);
            
            var isIsEncyclopediaRewardConditionAchieved = new EncyclopediaRewardConditionAchievedFlag(IsUpdateEncyclopediaReward());

            return new UnitEnhanceGradeUpDialogModel(
                mstUnit.RoleType,
                mstUnit.AssetKey,
                beforeStatus.HP,
                afterStatus.HP,
                beforeStatus.AttackPower,
                afterStatus.AttackPower,
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                isIsEncyclopediaRewardConditionAchieved);
        }

        bool IsUpdateEncyclopediaReward()
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var totalGrade = userUnits.Aggregate(new UnitGrade(0), (acc, unit) => acc + unit.Grade);
            return MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards()
                .Any(mst => mst.UnitEncyclopediaRank == totalGrade);
        }
    }
}
