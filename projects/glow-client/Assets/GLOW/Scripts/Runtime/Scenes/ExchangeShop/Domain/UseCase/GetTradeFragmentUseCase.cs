using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class GetTradeFragmentUseCase
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public IReadOnlyList<ItemModel> GetTradeFragments()
        {
            var userItems = GameRepository.GetGameFetchOther().UserItemModels;
            var items = userItems
                .Select(userItem =>
                {
                    var mstItem = MstItemDataRepository.GetItem(userItem.MstItemId);
                    return ItemModelTranslator.ToItemModel(userItem, mstItem);
                })
                .Where(item => item.Type == ItemType.CharacterFragment)
                .Where(item => item.Amount.Value > 0)
                .OrderByDescending(item => item.Rarity)
                .ThenBy(item => item.Id)
                .ToList();

            return items;
        }
    }
}
