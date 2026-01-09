using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.ItemDetail.Domain.Models;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public record GetFragmentLineupUseCaseModel(
        IReadOnlyList<MstItemModel> LineupItemModelList,
        ItemDetailAvailableLocationModel AvailableLocationModel);
}