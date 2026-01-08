using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.OutpostEnhance.Presentation.ViewModels
{
    public record OutpostEnhanceViewModel(
        HP OutpostHp,
        IReadOnlyList<OutpostEnhanceTypeButtonViewModel> Buttons);
}
