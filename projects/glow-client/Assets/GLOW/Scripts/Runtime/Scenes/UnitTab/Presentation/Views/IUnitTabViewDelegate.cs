namespace GLOW.Scenes.UnitTab.Presentation.Views
{
    public interface IUnitTabViewDelegate
    {
        void ViewDidLoad();
        void UnloadView();
        void UnitListTabSelect();
        void PartyFormationTabSelect();
        void OutpostEnhanceTabSelect();
        void ArtworkFormationTabSelect();
        void ArtworkListTabSelect();
        void ViewWillAppear();
    }
}
