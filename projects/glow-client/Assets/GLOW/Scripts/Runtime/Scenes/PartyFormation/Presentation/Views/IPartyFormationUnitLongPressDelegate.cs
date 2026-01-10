using GLOW.Core.Domain.ValueObjects;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public interface IPartyFormationUnitLongPressDelegate
    {
        void OnLongPress(PointerEventData eventData, UserDataId userUnitId, UnitImageAssetPath imageAssetPath);
        void OnLongPressUp(UserDataId userUnitId);
        void OnPressLock(int index);
    }
}
