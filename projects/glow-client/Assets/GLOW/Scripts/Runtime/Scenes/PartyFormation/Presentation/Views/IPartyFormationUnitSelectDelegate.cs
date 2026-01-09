using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public interface IPartyFormationUnitSelectDelegate
    {
        void ShowUnitEnhanceView(UserDataId userUnitId);
        void SelectAssignUnit(UserDataId userUnitId, bool isAchievedSpecialRule);
        void SelectUnassignUnit(UserDataId userUnitId);
    }
}
