using GLOW.Core.Domain.ValueObjects;
namespace GLOW.Scenes.HomePartyFormation.Presentation.Views
{
    public interface IHomePartyFormationVIewDelegate
    {
        void OnViewDidLoad();
        void OnViewWillAppear(PartyNo partyNo);
        void OnViewDidUnload();
        void ShowUnitEnhanceView(UserDataId userUnitId, PartyNo currentPartyNo);
        void ShowPartyMemberSlotUnlockCondition(int index);
        void SelectAssignUnit(PartyNo partyNo, UserDataId userUnitId, bool isAchievedSpecialRule);
        void SelectUnassignUnit(PartyNo partyNo, UserDataId userUnitId);
        void DropPartyUnit(PartyNo partyNo, PartyMemberIndex index, UserDataId userUnitId);
        void PartyNameEdit(PartyNo partyNo);
        void SetUpPartyUnitList(PartyNo currentParty);

        void OnSortAscending(PartyNo partyNo);
        void OnSortDescending(PartyNo partyNo);
        void OnSortAndFilter(PartyNo partyNo);
        void OnCloseButtonTapped();

        void OnInGameSpecialRule();
        void OnRecommendedFormation(PartyNo partyNo);
    }
}
