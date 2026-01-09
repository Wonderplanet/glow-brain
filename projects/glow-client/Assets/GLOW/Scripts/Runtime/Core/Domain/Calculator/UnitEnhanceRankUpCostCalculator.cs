using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.Models;
using Zenject;

namespace GLOW.Core.Domain.Calculator
{
    public class UnitEnhanceRankUpCostCalculator : IUnitEnhanceRankUpCostCalculator
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public IReadOnlyList<UnitEnhanceCostItemModel> CalculateRankUpCosts(MstCharacterModel mstUnit, UserUnitModel userUnit)
        {
            return CalculateRankUpCostsForLevel(mstUnit, userUnit.Level);
        }

        public IReadOnlyList<UnitEnhanceCostItemModel> CalculateRankUpCostsForLevel(MstCharacterModel mstUnit, UnitLevel unitLevel)
        {
            var rankUpColorMemoryAmount = ItemAmount.Zero;
            var rankUpUnitMemoryAmount = ItemAmount.Zero;
            var srMemoryFragmentAmount = ItemAmount.Zero;
            var ssrMemoryFragmentAmount = ItemAmount.Zero;
            var urMemoryFragmentAmount = ItemAmount.Zero;

            var mstItems = MstItemDataRepository.GetItems();
            var materialItems = mstItems
                .Where(mst => mst.Type == ItemType.RankUpMaterial && !mst.EffectValue.IsEmpty())
                .ToList();

            // 個別のランクアップ素材指定があればそちらを、なければユニットラベル毎の汎用設定を使う
            if (mstUnit.HasSpecificRankUp)
            {
                var mstSpecificRankUp = MstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(mstUnit.Id)
                    .Find(mst => mst.RequireLevel == unitLevel);
                rankUpColorMemoryAmount = mstSpecificRankUp.ColorMemoryAmount;
                rankUpUnitMemoryAmount = mstSpecificRankUp.UnitMemoryAmount;
                srMemoryFragmentAmount = mstSpecificRankUp.SrMemoryFragmentAmount;
                ssrMemoryFragmentAmount = mstSpecificRankUp.SsrMemoryFragmentAmount;
                urMemoryFragmentAmount = mstSpecificRankUp.UrMemoryFragmentAmount;
            }
            else
            {
                var mstRankUp = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                    .Find(rankUp => rankUp.RequireLevel == unitLevel);
                rankUpColorMemoryAmount = mstRankUp.ColorMemoryAmount;
                srMemoryFragmentAmount = mstRankUp.SrMemoryFragmentAmount;
                ssrMemoryFragmentAmount = mstRankUp.SsrMemoryFragmentAmount;
                urMemoryFragmentAmount = mstRankUp.UrMemoryFragmentAmount;
            }

            var costItems = new List<UnitEnhanceCostItemModel>();
            var userItems = GameRepository.GetGameFetchOther().UserItemModels;

            // 属性ごとのカラーメモリー
            if (!rankUpColorMemoryAmount.IsZero())
            {
                var mstItem = GetRankUpColorMaterial(materialItems, mstUnit.Color);
                var userItem = userItems.FirstOrDefault(item => item.MstItemId == mstItem.Id) ?? UserItemModel.Empty;
                costItems.Add(new UnitEnhanceCostItemModel(ItemModelTranslator.ToItemModel(mstItem, rankUpColorMemoryAmount), userItem.Amount));
            }
            // イベントキャラとかの個別素材
            if (!rankUpUnitMemoryAmount.IsZero())
            {
                var mstItem = GetRankUpUnitMaterial(materialItems, mstUnit.Id);
                var userItem = userItems.FirstOrDefault(item => item.MstItemId == mstItem.Id) ?? UserItemModel.Empty;
                costItems.Add(new UnitEnhanceCostItemModel(ItemModelTranslator.ToItemModel(mstItem, rankUpUnitMemoryAmount), userItem.Amount));
            }

            // 各メモリーフラグメント
            if (!srMemoryFragmentAmount.IsZero())
            {
                var mstItem = GetMemoryFragment(mstItems, Rarity.SR);
                var userItem = userItems.FirstOrDefault(item => item.MstItemId == mstItem.Id) ?? UserItemModel.Empty;
                costItems.Add(new UnitEnhanceCostItemModel(ItemModelTranslator.ToItemModel(mstItem, srMemoryFragmentAmount), userItem.Amount));
            }
            if (!ssrMemoryFragmentAmount.IsZero())
            {
                var mstItem = GetMemoryFragment(mstItems, Rarity.SSR);
                var userItem = userItems.FirstOrDefault(item => item.MstItemId == mstItem.Id) ?? UserItemModel.Empty;
                costItems.Add(new UnitEnhanceCostItemModel(ItemModelTranslator.ToItemModel(mstItem, ssrMemoryFragmentAmount), userItem.Amount));
            }
            if (!urMemoryFragmentAmount.IsZero())
            {
                var mstItem = GetMemoryFragment(mstItems, Rarity.UR);
                var userItem = userItems.FirstOrDefault(item => item.MstItemId == mstItem.Id) ?? UserItemModel.Empty;
                costItems.Add(new UnitEnhanceCostItemModel(ItemModelTranslator.ToItemModel(mstItem, urMemoryFragmentAmount), userItem.Amount));
            }

            return costItems;
        }

        MstItemModel GetRankUpColorMaterial(IReadOnlyList<MstItemModel> mstItems, CharacterColor color)
        {
            return mstItems
                .Find(mst => mst.EffectValue.ToCharacterColor() == color);
        }

        MstItemModel GetRankUpUnitMaterial(IReadOnlyList<MstItemModel> mstItems, MasterDataId mstUnitId)
        {
            return mstItems
                .FirstOrDefault(mst => mst.EffectValue.ToMasterDataId() == mstUnitId, MstItemModel.Empty);
        }

        MstItemModel GetMemoryFragment(IReadOnlyList<MstItemModel> mstItems, Rarity rarity)
        {
            return mstItems
                .FirstOrDefault(mst => mst.Type == ItemType.RankUpMemoryFragment && mst.Rarity == rarity);
        }
    }
}
