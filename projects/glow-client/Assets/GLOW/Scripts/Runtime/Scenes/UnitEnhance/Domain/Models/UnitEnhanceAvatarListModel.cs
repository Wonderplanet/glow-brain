using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceAvatarListModel(IReadOnlyList<MasterDataId> UnitList, MasterDataId PresentationUnitId);
}
