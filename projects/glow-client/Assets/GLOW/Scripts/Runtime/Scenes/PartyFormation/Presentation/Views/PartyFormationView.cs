using GLOW.Scenes.UnitTab.Presentation.Views.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationView : UIView
    {
        [SerializeField] PartyFormationPageView _partyPageView;
        [SerializeField] PartyFormationUnitListComponent _unitListComponent;
        [SerializeField] PartyFormationPartyListComponent _partyListComponent;
        [SerializeField] PartyFormationFloatingAvatarComponent _floatingAvatarComponent;
        [SerializeField] PartyFormationScrollRect _scrollRect;
        [SerializeField] UnitListFilterAndSortComponent _filterAndSortComponent;
        [SerializeField] GameObject _inGameSpecialRuleButton;

        public PartyFormationPageView PageView => _partyPageView;
        public PartyFormationUnitListComponent UnitListComponent => _unitListComponent;
        public PartyFormationPartyListComponent PartyListComponent => _partyListComponent;
        public PartyFormationFloatingAvatarComponent FloatingAvatarComponent => _floatingAvatarComponent;
        public PartyFormationScrollRect ScrollRect => _scrollRect;
        public UnitListFilterAndSortComponent FilterAndSortComponent => _filterAndSortComponent;

        public void SetInGameSpecialRuleButtonVisible(bool isShow)
        {
            _inGameSpecialRuleButton.gameObject.SetActive(isShow);
        }

    }
}
