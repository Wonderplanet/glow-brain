using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitDetail.Domain.Models;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitDetail.Domain.UseCases
{
    public class GetUnitMaxStatusDetailUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject]  IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitGradeUpRepository MstUnitGradeUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceAbilityModelListFactory UnitEnhanceAbilityModelListFactory { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public UnitDetailModel GetUnitDetail(MasterDataId masterDataId)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(masterDataId);
            var unitLevelCap = new UnitLevel(MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToInt());
            
            // レベルキャップで到達可能な最大ランクを取得
            var rankUpList = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel);
            var maxRankUpModel = rankUpList.MaxByBelowOrEqualUpperLimit(rank => rank.RequireLevel, unitLevelCap);
            var maxRank = maxRankUpModel?.Rank ?? UnitRank.Min; // 到達可能なランクがない場合はUnitRank.Minを使用
                    
            var maxGrade = MstUnitGradeUpRepository.GetUnitGradeUpList(mstUnit.UnitLabel)
                .OrderByDescending(grade => grade.GradeLevel)
                .First();

            var characterImagePath = UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey);

            var calculateStatus = UnitStatusCalculateHelper.Calculate(
                mstUnit, 
                unitLevelCap, 
                maxRank, 
                maxGrade.GradeLevel);

            var abilities = UnitEnhanceAbilityModelListFactory.Create(mstUnit, maxRank);
            
            var detailModel = new UnitEnhanceUnitDetailModel(mstUnit.Detail);
            
            var statusModel = new UnitEnhanceUnitStatusModel(
                calculateStatus.HP, 
                calculateStatus.AttackPower, 
                mstUnit.AttackRangeType, 
                mstUnit.UnitMoveSpeed);

            var currentSpecialAttack = TranslateSpecialAttack(mstUnit, maxGrade.GradeLevel, unitLevelCap);
            
            return new UnitDetailModel(
                characterImagePath,
                mstUnit.Name,
                mstUnit.RoleType,
                mstUnit.Rarity,
                mstUnit.Color,
                unitLevelCap,
                unitLevelCap,
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstUnit.SeriesAssetKey.Value)),
                currentSpecialAttack,
                calculateStatus.HP,
                calculateStatus.AttackPower,
                mstUnit.SummonCost,
                mstUnit.AttackRangeType,
                mstUnit.UnitMoveSpeed,
                mstUnit.AttackCountPerMinute,
                maxRank,
                maxGrade.GradeLevel,
                mstUnit.SummonCoolTime,
                abilities,
                detailModel,
                statusModel,
                MaxStatusFlag.True);
        }

        UnitEnhanceSpecialAttackModel TranslateSpecialAttack(MstCharacterModel mstUnit, UnitGrade unitGrade, UnitLevel unitLevel)
        {
            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(mstUnit, unitGrade, unitLevel);
            
            return new UnitEnhanceSpecialAttackModel(
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                mstUnit.SpecialAttackInitialCoolTime.ToSpecialAttackCoolTime(),
                mstUnit.SpecialAttackCoolTime.ToSpecialAttackCoolTime(),
                mstUnit.RoleType
            );
        }
    }
}
