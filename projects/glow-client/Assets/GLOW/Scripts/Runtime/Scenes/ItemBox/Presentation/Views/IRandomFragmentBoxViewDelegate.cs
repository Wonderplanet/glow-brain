using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    internal interface IRandomFragmentBoxViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnload();

        void OnCancelSelected();
        void OnUseSelected(ItemAmount amount);
        void OnLineupSelected();
    }
}
