using System;
using System.Collections.Generic;
using System.Globalization;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.ItemBox.Domain.Models;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;

namespace GLOW.Scenes.ItemBox.Presentation.Translators
{
    public class ItemBoxViewModelTranslator
    {
        public static ItemBoxIconListViewModel ToItemBoxIconListViewModel(
            IReadOnlyList<ItemBoxModel> itemModels,
            ItemBoxTabType itemBoxTabType)
        {
            return itemBoxTabType switch
            {
                ItemBoxTabType.Item => ToItemViewModels(itemModels),
                ItemBoxTabType.Enhance => ToEnhanceItemViewModels(itemModels),
                ItemBoxTabType.CharacterFragment => ToCharacterFragmentViewModels(itemModels),
            };
        }

        // キャラのかけら
        static ItemBoxIconListViewModel ToCharacterFragmentViewModels(IReadOnlyList<ItemBoxModel> itemModels)
        {
            var viewModels =  itemModels
                .Where(model => model.ItemModel.IsCharacterFragmentType())
                .OrderBy(model => model.ItemModel.Id)
                .Select(model => ItemViewModelTranslator.ToItemIconViewModel(model.ItemModel))
                .ToList();
            return new ItemBoxIconListViewModel(viewModels);
        }

        // 強化アイテム
        static ItemBoxIconListViewModel ToEnhanceItemViewModels(IReadOnlyList<ItemBoxModel> itemModels)
        {
            // メモリーフラグメント
            var memoryFraguments = itemModels
                .Where(model => model.ItemModel.IsRankUpMemoryFragmentType())
                .OrderByDescending(model => model.ItemModel.Rarity)
                .Select(model => ItemViewModelTranslator.ToItemIconViewModel(model.ItemModel))
                .ToList();

            // カラーメモリー
            var colorMemorys = itemModels
                .Where(model => model.ItemModel.IsRankUpMaterialType())
                .OrderByDescending(model => model.ItemModel.Rarity)
                .Select(model => ItemViewModelTranslator.ToItemIconViewModel(model.ItemModel))
                .ToList();

            return new ItemBoxIconListViewModel(memoryFraguments.Concat(colorMemorys).ToList());
        }

        // キャラのかけら・強化アイテム以外
        static ItemBoxIconListViewModel ToItemViewModels(IReadOnlyList<ItemBoxModel> itemModels)
        {
            // かけらBOX
            var fragmentBoxModels = itemModels
                .Where(model => model.ItemModel.IsFragmentBoxType())
                .OrderByDescending(model => model.ItemModel.Rarity)
                .Select(model => ItemViewModelTranslator.ToItemIconViewModel(model.ItemModel))
                .ToList();

            // ガチャチケット
            var gachaTicketModels = itemModels
                .Where(model => model.ItemModel.IsGachaTicketType())
                .OrderByDescending(model => model.ItemModel.Rarity)
                .ThenBy(model => model.ItemModel.Id)
                .Select(model => ItemViewModelTranslator.ToItemIconViewModel(model.ItemModel))
                .ToList();

            // その他
            var otherModels = itemModels
                .Where(model =>
                    !model.ItemModel.IsCharacterFragmentType()
                    && !model.ItemModel.IsFragmentBoxType()
                    && !model.ItemModel.IsGachaTicketType())
                .OrderByDescending(model => model.ItemModel.Rarity)
                .ThenBy(model => model.ItemModel.Id)
                .Select(model => ItemViewModelTranslator.ToItemIconViewModel(model.ItemModel))
                .ToList();

            return new ItemBoxIconListViewModel(fragmentBoxModels.Concat(gachaTicketModels).Concat(otherModels).ToList());
        }

        static int GetFragmentBoxSortOrder(ItemType type)
        {
            switch (type)
            {
                case ItemType.RandomFragmentBox:
                    return 1;
                case ItemType.SelectionFragmentBox:
                    return 2;
                case ItemType.SeriesFragmentBox:
                    return 3;
                default:
                    return 4;
            }
        }
    }
}
