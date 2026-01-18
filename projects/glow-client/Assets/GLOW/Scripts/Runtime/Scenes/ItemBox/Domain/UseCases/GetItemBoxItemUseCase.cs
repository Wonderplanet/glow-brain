using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class GetItemBoxItemUseCase
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public ItemModel GetItem(MasterDataId itemId)
        {
            var userItems = GameRepository.GetGameFetchOther().UserItemModels;
            var userItem = userItems.Find(item => item.MstItemId == itemId);

            if (userItem == default)
            {
                return ItemModel.Empty;
            }

            var mstItem = MstItemDataRepository.GetItem(userItem.MstItemId);
            return ItemModelTranslator.ToItemModel(userItem, mstItem);
        }
    }
}
