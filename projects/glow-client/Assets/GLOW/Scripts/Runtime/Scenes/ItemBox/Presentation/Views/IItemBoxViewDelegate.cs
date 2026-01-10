using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    internal interface IItemBoxViewDelegate
    {
        void OnViewDidLoad();
        void ViewDidUnload();
        void OnBackSelected();
        void OnItemGroupSelected(ItemBoxTabType itemBoxTabType);
        void OnItemSelected(MasterDataId itemId);
    }
}
