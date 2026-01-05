using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.UnitEnhance;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Domain.UseCases
{
    public class UnitEnhanceRankUpDetailDialogUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceRankUpCostCalculator UnitEnhanceRankUpCostCalculator { get; }

        public UnitEnhanceRankUpDetailDialogModel GetUnitEnhanceRankUpDetailDialogModel(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var rankList = new List<RankUpInfoModel>();
            if (mstUnit.HasSpecificRankUp)
            {
                rankList = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstUnit.Id)
                    .OrderBy(rank => rank.Rank)
                    .Select(rank => rank.ToRankUpInfoModel())
                    .ToList();
            }
            else
            {
                rankList = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                    .OrderBy(rank => rank.Rank)
                    .Select(rank => rank.ToRankUpInfoModel())
                    .ToList();
            }

            List<UnitEnhanceRankUpDetailCellModel> cellModelList = new List<UnitEnhanceRankUpDetailCellModel>();
            for (int i = 0; i < rankList.Count; i++)
            {
                var prevRank = RankUpInfoModel.Empty;
                if (i > 0)
                {
                    prevRank = rankList[i - 1];
                }

                var nextRank = RankUpInfoModel.Empty;
                if (rankList.Count > i + 1)
                {
                    nextRank = rankList[i + 1];
                }
                cellModelList.Add(TranslateCellModel(rankList[i], prevRank, nextRank, mstUnit, userUnit));
            }

            var sortCellModelList = cellModelList
                .OrderBy(cell => cell.Rank > userUnit.Rank ? 0 : 1)
                .ThenBy(cell => cell.RequiredLevel)
                .ToList();

            return new UnitEnhanceRankUpDetailDialogModel(sortCellModelList);
        }

        UnitEnhanceRankUpDetailCellModel TranslateCellModel(RankUpInfoModel rankUp, RankUpInfoModel prevRank, RankUpInfoModel nextRank, MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            var mstItem = MstItemDataRepository.GetItems()
                .Where(mst => mst.Type == ItemType.RankUpMaterial && !mst.EffectValue.IsEmpty())
                .Find(mst => mst.EffectValue.ToCharacterColor() == mstUnit.Color);

            var unitLevelCap = new UnitLevel(MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToInt());
            var maxLevel = nextRank.IsEmpty() ? unitLevelCap : nextRank.RequireLevel;

            var beforeRank = new UnitRank((rankUp.Rank.Value - 1) < 0 ? 0 : rankUp.Rank.Value - 1);
            var beforeCalculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, rankUp.RequireLevel, beforeRank, userUnit.Grade);
            var afterCalculateStatus = UnitStatusCalculateHelper.Calculate(mstUnit, rankUp.RequireLevel, rankUp.Rank, userUnit.Grade);

            var addHp = afterCalculateStatus.HP - beforeCalculateStatus.HP;
            var addAttackPower = afterCalculateStatus.AttackPower - beforeCalculateStatus.AttackPower;

            var newlyUnlockedAbilities = mstUnit.GetNewlyUnlockedUnitAbilities(rankUp.Rank);

            var isComplete = false;
            var isLocked = false;

            // すでに開放済みかどうか
            if (rankUp.Rank <= userUnit.Rank)
            {
                isComplete = true;
            }
            // 次のランクアップ項目かどうか
            else if (prevRank != null && prevRank.Rank > userUnit.Rank)
            {
                isLocked = true;
            }

            var requireItems = CreateRankUpModel(mstUnit, rankUp.RequireLevel);

            return new UnitEnhanceRankUpDetailCellModel(
                mstUnit.RoleType,
                rankUp.Rank,
                maxLevel,
                rankUp.RequireLevel,
                requireItems,
                afterCalculateStatus.HP,
                addHp,
                afterCalculateStatus.AttackPower,
                addAttackPower,
                newlyUnlockedAbilities,
                isComplete,
                isLocked);
        }

        IReadOnlyList<UnitEnhanceRequireItemModel> CreateRankUpModel(
            MstCharacterModel mstUnit,
            UnitLevel unitLevel)
        {
            return UnitEnhanceRankUpCostCalculator.CalculateRankUpCostsForLevel(mstUnit, unitLevel)
                .Select(model => new UnitEnhanceRequireItemModel(model.Item, new EnoughCostFlag(model.Item.Amount <= model.PossessionAmount)))
                .ToList();
        }
    }
}
