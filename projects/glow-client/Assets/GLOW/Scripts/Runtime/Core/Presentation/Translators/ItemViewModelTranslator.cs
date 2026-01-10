using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Translators
{
    public static class ItemViewModelTranslator
    {
        public static IReadOnlyList<ItemIconViewModel> ToItemIconViewModels(IReadOnlyList<ItemModel> itemModels)
        {
            return itemModels
                .Select(ToItemIconViewModel)
                .ToList();
        }

        public static ItemIconViewModel ToItemIconViewModel(ItemModel itemModel)
        {
            return new ItemIconViewModel(
                itemModel.Id,
                ItemIconAssetPath.FromAssetKey(itemModel.ItemAssetKey),
                itemModel.Rarity,
                itemModel.Amount);
        }

        public static ItemDetailViewModel ToItemDetailViewModel(ItemModel itemModel)
        {
            return new ItemDetailViewModel(
                itemModel.Name,
                itemModel.Description,
                ItemIconAssetPath.FromAssetKey(itemModel.ItemAssetKey),
                itemModel.Rarity,
                itemModel.Amount);
        }
    }
}
