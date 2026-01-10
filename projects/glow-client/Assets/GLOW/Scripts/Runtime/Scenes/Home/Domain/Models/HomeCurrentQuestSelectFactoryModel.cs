using System.Collections.Generic;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeCurrentQuestSelectFactoryModel(
        IReadOnlyList<HomeCurrentQuestSelectFactoryItemModel> Items);
}
