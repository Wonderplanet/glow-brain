using UIKit;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationOneLinePartyViewController : PartyFormationPartyViewController
    {
        public override void LoadView()
        {
            PrefabName = "PartyFormationPartyView_Onestage";
            base.LoadView();
        }
    }
}
