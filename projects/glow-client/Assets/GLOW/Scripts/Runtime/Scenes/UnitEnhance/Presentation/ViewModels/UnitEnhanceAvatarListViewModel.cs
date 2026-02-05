using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Presentation.ViewModels
{
    public record UnitEnhanceAvatarListViewModel(IReadOnlyList<MasterDataId> UnitList, MasterDataId PresentationUnitId);
}
