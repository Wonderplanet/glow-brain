using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public interface IPartyFormationViewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear(PartyNo partyNo);
        void OnViewWillDisappear();
        void ShowUnitEnhanceView(UserDataId userUnitId, PartyNo currentPartyNo);
        void ShowPartyMemberSlotUnlockCondition(int index);
        void SelectAssignUnit(PartyNo partyNo, UserDataId userUnitId);
        void SelectUnassignUnit(PartyNo partyNo, UserDataId userUnitId);
        void DropPartyUnit(PartyNo partyNo, PartyMemberIndex index, UserDataId userUnitId);
        void PartyNameEdit(PartyNo partyNo);
        void SetUpPartyUnitList(PartyNo currentParty);

        void OnSortAscending(PartyNo partyNo);
        void OnSortDescending(PartyNo partyNo);
        void OnSortAndFilter(PartyNo partyNo);
        void OnRecommendedFormation(PartyNo partyNo);
    }
}
