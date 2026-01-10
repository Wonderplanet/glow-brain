using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class GetUnitEnhanceLevelUpUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceNotificationHelper UnitEnhanceNotificationHelper { get; }
        [Inject] IUnitEnhanceRankUpCostCalculator UnitEnhanceRankUpCostCalculator { get; }

        public UnitEnhanceLevelUpTabModel GetLevelUpModel(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var levelCap = MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToInt();
            var enableLevelUp = levelCap > userUnit.Level.Value;
            var enableRankUp = IsEnableRankUp(mstUnit, userUnit);
            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var isGradeUp = UnitEnhanceNotificationHelper.GetUnitGradeUpNotification(userUnit);

            var levelUpModel = UnitEnhanceLevelUpModel.Empty;
            var rankUpModel = UnitEnhanceRankUpModel.Empty;

            // ランクアップの場合
            if (enableRankUp)
            {
                rankUpModel = CreateRankUpModel(mstUnit, userUnit);
            }
            // レベルアップの場合
            else if (enableLevelUp)
            {
                var userCoin = GameRepository.GetGameFetch().UserParameterModel.Coin;
                var nextLevel = MstUnitLevelUpRepository.GetUnitLevelUpList(mstUnit.UnitLabel)
                    .Find(level => level.Level == userUnit.Level + 1);

                var isCostEnough = new EnoughCostFlag(userCoin >= nextLevel.RequiredCoin);
                levelUpModel = new UnitEnhanceLevelUpModel(nextLevel.RequiredCoin, isCostEnough);
            }

            return new UnitEnhanceLevelUpTabModel(
                mstUnit.RoleType,
                levelUpModel,
                rankUpModel,
                calculateStatus.HP,
                calculateStatus.AttackPower,
                userUnit.Grade,
                isGradeUp);
        }

        UnitEnhanceRankUpModel CreateRankUpModel(
            MstCharacterModel mstUnit,
            UserUnitModel userUnit)
        {
            var costItems = UnitEnhanceRankUpCostCalculator.CalculateRankUpCosts(mstUnit, userUnit)
                .Select(model => new UnitEnhanceRequireItemModel(model.Item, new EnoughCostFlag(model.Item.Amount <= model.PossessionAmount)))
                .ToList();
            return new UnitEnhanceRankUpModel(costItems);
        }

        bool IsEnableRankUp(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            if (mstUnit.HasSpecificRankUp)
            {
                var specificRankUpList = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstUnit.Id);
                return specificRankUpList
                    .Any(rankUp => userUnit.Rank < rankUp.Rank && rankUp.RequireLevel == userUnit.Level);
            }

            var rankUpList = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel);
            return rankUpList
                .Any(rankUp => userUnit.Rank < rankUp.Rank && rankUp.RequireLevel == userUnit.Level);
        }
    }
}
