using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;

namespace GLOW.Scenes.UnitDetail.Presentation.ViewModels
{
    public record UnitDetailViewModel(UnitEnhanceUnitInfoViewModel UnitInfo, UnitEnhanceLevelUpTabViewModel LevelUpTab, MaxStatusFlag MaxStatusFlag);
}
