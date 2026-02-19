using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public interface ISelectionFragmentBoxViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidAppear();
        void OnViewDidUnload();

        void OnCancelSelected();
        void OnUseSelected(MasterDataId selectedItem, ItemAmount amount);
        void OnTapInfoButton();
    }
}
