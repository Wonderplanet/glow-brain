using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class UpdatePlacedItemsProcess : IUpdatePlacedItemsProcess
    {
        public UpdatePlacedItemsProcessResult Update(
            IReadOnlyList<PlacedItemModel> items)
        {
            var updatedPlacedItems = new List<PlacedItemModel>();
            var newPlacedItems = new List<PlacedItemModel>();
            var consumedItems = new List<PlacedItemModel>();
            
            foreach (var item in items)
            {
                // 効果発動済みのアイテムはリストから外す
                if (item.PlaceItemState == PlaceItemState.EffectConsumed)
                {
                    consumedItems.Add(item);
                    continue;
                }
            
                if (item.PlaceItemState == PlaceItemState.Placing)
                {
                    var updatedItem = item with
                    {
                        PlaceItemState = PlaceItemState.EffectAvailable
                    };
                    
                    newPlacedItems.Add(updatedItem);
                    continue;
                }
                
                // 効果発動前のアイテムはそのまま残す
                updatedPlacedItems.Add(item);
            }
            
            // 新規配置したアイテムも更新対象として追加
            updatedPlacedItems.AddRange(newPlacedItems);

            return new UpdatePlacedItemsProcessResult(newPlacedItems, updatedPlacedItems, consumedItems);
        }
    }
}