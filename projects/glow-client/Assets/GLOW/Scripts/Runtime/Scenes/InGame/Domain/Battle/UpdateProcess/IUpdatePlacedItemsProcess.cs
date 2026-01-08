using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IUpdatePlacedItemsProcess
    {
        UpdatePlacedItemsProcessResult Update(
            IReadOnlyList<PlacedItemModel> items);
    }
}