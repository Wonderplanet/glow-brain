using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterRarityModel(IReadOnlyList<Rarity> Rarities)
    {
        public static FilterRarityModel Default { get; } = new FilterRarityModel(new List<Rarity>());

        public bool IsAnyFilter => Rarities.Count > 0;

        public bool IsOn(Rarity type)
        {
            return Rarities.Contains(type);
        }
    }
}
