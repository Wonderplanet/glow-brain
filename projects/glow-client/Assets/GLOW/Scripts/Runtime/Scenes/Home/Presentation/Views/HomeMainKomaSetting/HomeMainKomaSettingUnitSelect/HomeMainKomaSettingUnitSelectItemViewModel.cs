using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation
{
    public record HomeMainKomaSettingUnitSelectItemViewModel(
        MasterDataId MstUnitId,
        CharacterIconAssetPath AssetPath,
        HomeMainKomaSettingUnitStatus Status
    )
    {
        public HomeMainKomaSettingUnitSelectItemViewModel
            CreateUpdateStatus(HomeMainKomaSettingUnitStatus updatedStatus)
        {
            return this with { Status = updatedStatus };
        }

        public bool IsSelected()
        {
            return Status == HomeMainKomaSettingUnitStatus.Selected;
        }

        public bool IsGrayout()
        {
            return Status == HomeMainKomaSettingUnitStatus.OtherSelected;
        }
    };
}
