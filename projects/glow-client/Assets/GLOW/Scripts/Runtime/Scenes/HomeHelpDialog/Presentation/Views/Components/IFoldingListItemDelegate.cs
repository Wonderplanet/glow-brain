namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components
{
    public interface IFoldingListItemDelegate
    {
        void OnSelect(int index);
        void OnBeginUpdateLayout();
        void OnUpdateLayout();
        void OnEndUpdateLayout();
    }
}
