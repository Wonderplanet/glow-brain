namespace GLOW.Scenes.UnitTab.Presentation.Views
{
    public interface IUnitTabViewDelegate
    {
        void ViewDidLoad();
        void UnloadView();
        void UnitListTabSelect();
        void PartyFormationTabSelect();
        void OutpostEnhanceTabSelect();
    }
}
