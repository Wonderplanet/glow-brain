using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitDetail.Domain.Models;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitDetail.Domain.UseCases
{
    public class GetUnitMinimumStatusDetailUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceAbilityModelListFactory UnitEnhanceAbilityModelListFactory { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public UnitDetailModel GetUnitDetail(MasterDataId masterDataId)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(masterDataId);
            var minRank =  new UnitRank(0);
            var minGrade = new UnitGrade(1);
            var minLevel = new UnitLevel(1);

            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, minLevel, minRank, minGrade);
            var statusModel = new UnitEnhanceUnitStatusModel(calculateStatus.HP, calculateStatus.AttackPower, mstUnit.AttackRangeType, mstUnit.UnitMoveSpeed);

            var characterImagePath = UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey);
            var abilities = UnitEnhanceAbilityModelListFactory.Create(mstUnit, minRank);
            var detailModel = new UnitEnhanceUnitDetailModel(mstUnit.Detail);

            var nextRank = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                .Where(mst => minRank < mst.Rank)
                .OrderBy(mst => mst.Rank)
                .First();
            var levelLimit =  nextRank.RequireLevel;

            var currentSpecialAttack = TranslateSpecialAttack(mstUnit, minGrade);
            
            return new UnitDetailModel(
                characterImagePath,
                mstUnit.Name,
                mstUnit.RoleType,
                mstUnit.Rarity,
                mstUnit.Color,
                minLevel,
                levelLimit,
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstUnit.SeriesAssetKey.Value)),
                currentSpecialAttack,
                calculateStatus.HP,
                calculateStatus.AttackPower,
                mstUnit.SummonCost,
                mstUnit.AttackRangeType,
                mstUnit.UnitMoveSpeed,
                mstUnit.AttackCountPerMinute,
                minRank,
                minGrade,
                mstUnit.SummonCoolTime,
                abilities,
                detailModel,
                statusModel,
                MaxStatusFlag.False);
        }

        UnitEnhanceSpecialAttackModel TranslateSpecialAttack(MstCharacterModel mstUnit, UnitGrade unitGrade)
        {
            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(
                mstUnit, 
                unitGrade, 
                UnitLevel.One);
            
            return new UnitEnhanceSpecialAttackModel(
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                mstUnit.SpecialAttackInitialCoolTime.ToSpecialAttackCoolTime(),
                mstUnit.SpecialAttackCoolTime.ToSpecialAttackCoolTime(),
                mstUnit.RoleType);
        }
    }
}
