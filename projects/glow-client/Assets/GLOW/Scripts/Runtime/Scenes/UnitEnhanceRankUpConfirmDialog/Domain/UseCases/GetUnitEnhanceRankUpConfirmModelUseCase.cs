using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.UnitEnhance;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.Models;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.UseCases
{
    public class GetUnitEnhanceRankUpConfirmModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceRankUpCostCalculator UnitEnhanceRankUpCostCalculator { get; }

        public UnitEnhanceRankUpConfirmModel GetModel(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            // 今回のランクアップと次のランクアップ情報取得
            var rankUpInfos = GetRankUpInfo(mstUnit, userUnit);
            var currentRankUp = rankUpInfos.CurrentRankUp;
            var nextRankUp = rankUpInfos.NextRankUp;

            // RequireLevelが該当ランクに達するための必要レベルのため、
            // これまでのレベル制限(beforeMaxLevel)が今回のランクアップ後のランクのRequireLevelで、
            // ランクアップ後のレベル制限(afterMaxLevel)が次のランクのRequireLevelになる
            var unitLevelCap = new UnitLevel(MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToInt());
            var beforeMaxLevel = unitLevelCap <= userUnit.Level || currentRankUp == null ? unitLevelCap : currentRankUp.RequireLevel;
            var afterMaxLevel = nextRankUp.IsEmpty() ? unitLevelCap : nextRankUp.RequireLevel;

            // afterStatusやアビリティは今回のランクアップの情報で計算をする
            var beforeStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var afterStatusRank = currentRankUp != null ? currentRankUp.Rank : userUnit.Rank;
            var afterStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, afterStatusRank, userUnit.Grade);

            var newlyUnlockedUnitAbilities = mstUnit.GetNewlyUnlockedUnitAbilities(afterStatusRank);

            var costItemModels = UnitEnhanceRankUpCostCalculator.CalculateRankUpCosts(mstUnit, userUnit);
            var enableConfirm = UnitRankUpEnableConfirm.Create(costItemModels.All(cost => cost.Item.Amount <= cost.PossessionAmount));

            return new UnitEnhanceRankUpConfirmModel(
                costItemModels,
                mstUnit.RoleType,
                beforeMaxLevel,
                afterMaxLevel,
                beforeStatus.HP,
                afterStatus.HP,
                beforeStatus.AttackPower,
                afterStatus.AttackPower,
                newlyUnlockedUnitAbilities,
                enableConfirm
            );
        }

        (RankUpInfoModel CurrentRankUp, RankUpInfoModel NextRankUp) GetRankUpInfo(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var currentRankUpInfo = RankUpInfoModel.Empty;
            var nextRankUpInfo = RankUpInfoModel.Empty;

            if (mstUnit.HasSpecificRankUp)
            {
                var rankUps = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstUnit.Id);
                var currentRankUp = rankUps.FirstOrDefault(rank => userUnit.Rank < rank.Rank, MstUnitSpecificRankUpModel.Empty);
                var nextRankUp = rankUps.FirstOrDefault(rank => userUnit.Rank + 1 < rank.Rank, MstUnitSpecificRankUpModel.Empty);

                currentRankUpInfo = currentRankUp.ToRankUpInfoModel();
                nextRankUpInfo = nextRankUp.ToRankUpInfoModel();
            }
            else
            {
                var rankUps = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel);
                var currentRankUp = rankUps.FirstOrDefault(rank => userUnit.Rank < rank.Rank, MstUnitRankUpModel.Empty);
                var nextRankUp = rankUps.FirstOrDefault(rank => userUnit.Rank + 1 < rank.Rank, MstUnitRankUpModel.Empty);

                currentRankUpInfo = currentRankUp.ToRankUpInfoModel();
                nextRankUpInfo = nextRankUp.ToRankUpInfoModel();
            }

            return (currentRankUpInfo, nextRankUpInfo);
        }
    }
}
