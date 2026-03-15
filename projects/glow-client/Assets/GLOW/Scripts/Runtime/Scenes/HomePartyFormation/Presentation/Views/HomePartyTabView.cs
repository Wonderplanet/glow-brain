using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.HomePartyFormation.Presentation.Views
{
    public class HomePartyTabView : UIView
    {
        [SerializeField] UIToggleableComponentGroup _tabGroup;
        [SerializeField] RectTransform _contentRoot;

        const string _unitPartyListTabKey = "UnitPartyFormation";
        const string _artworkPartyTabKey = "ArtworkPartyFormation";

        public RectTransform ContentRoot => _contentRoot;

        public string UnitPartyListTabKey => _unitPartyListTabKey;
        public string ArtworkPartyTabKey => _artworkPartyTabKey;

        public void SetTabOn(string key)
        {
            _tabGroup.SetToggleOn(key);
        }
    }
}
