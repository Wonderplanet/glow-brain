using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterColorModel(IReadOnlyList<CharacterColor> FilterColors)
    {
        public static FilterColorModel Default { get; } = new FilterColorModel(new List<CharacterColor>());

        public bool IsAnyFilter => FilterColors.Count > 0;

        public bool IsOn(CharacterColor type)
        {
            return FilterColors.Contains(type);
        }
    }
}
