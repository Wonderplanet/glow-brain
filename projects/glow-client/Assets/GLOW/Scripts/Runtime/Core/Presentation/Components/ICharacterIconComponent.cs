using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Core.Presentation.Components
{
    public interface ICharacterIconComponent
    {
        void Setup(
            CharacterIconViewModel viewModel,
            bool isAssigned = false,
            UnitListSortType sortType = UnitListSortType.Rarity);
    }
}
