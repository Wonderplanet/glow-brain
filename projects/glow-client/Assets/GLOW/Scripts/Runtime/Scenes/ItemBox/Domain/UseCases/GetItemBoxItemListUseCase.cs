using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Domain.Models;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class GetItemBoxItemListUseCase
    {
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public IReadOnlyList<ItemBoxModel> GetItemList(ItemBoxTabType itemBoxTabType)
        {
            var userItems = GameRepository.GetGameFetchOther().UserItemModels;
            var items = userItems
                .Select(userItem =>
                    {
                        var mstItem = MstItemDataRepository.GetItem(userItem.MstItemId);
                        var itemModel = ItemModelTranslator.ToItemModel(userItem, mstItem);
                        return new ItemBoxModel(
                            itemModel,
                            itemModel.MstSeriesId != MasterDataId.Empty
                                ? MstSeriesDataRepository.GetMstSeriesModel(itemModel.MstSeriesId)
                                : MstSeriesModel.Empty);
                    })
                .Where(item => IsTargetType(item.ItemModel, itemBoxTabType))
                .Where(item => IsActiveItem(item))
                .Where(item => 0 < item.ItemModel.Amount.Value)
                .OrderBy(item => item.ItemModel.SortOrder)
                .ToList();

            return items;
        }

        bool IsActiveItem(ItemBoxModel model)
        {
            if (model.ItemModel.EndAt.IsUnlimitedEndAt) return true;

            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                model.ItemModel.StartAt,
                model.ItemModel.EndAt);
        }

        bool IsTargetType(ItemModel itemModel, ItemBoxTabType itemBoxTabType)
        {
            return itemBoxTabType switch
            {
                ItemBoxTabType.Item => itemModel.IsFragmentBoxType() || itemModel.IsItemType(),
                ItemBoxTabType.Enhance => itemModel.IsEnhanceItemType(),
                ItemBoxTabType.CharacterFragment => itemModel.IsCharacterFragmentType(),
                _ => false,
            };
        }

    }
}
