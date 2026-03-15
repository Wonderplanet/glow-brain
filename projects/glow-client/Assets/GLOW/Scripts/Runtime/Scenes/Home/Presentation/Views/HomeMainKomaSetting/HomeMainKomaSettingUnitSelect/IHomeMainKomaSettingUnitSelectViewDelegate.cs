using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation
{
    public interface IHomeMainKomaSettingUnitSelectViewDelegate
    {
        void OnViewDidLoad();
        void UpdateSelectingUnit(MasterDataId mstUnitId);
        void OnFilterButtonTapped(MasterDataId mstUnitId);
    }
}
