using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionBonusPointProgressIconComponent : UIBehaviour
    {
        [SerializeField] UIText _currentProgressText;

        public void Setup(BonusPoint currentPoint)
        {
            _currentProgressText.SetText(currentPoint.ToStringSeparated());
        }

        public void Setup(LoginDayCount currentLoginDayCount)
        {
            _currentProgressText.SetText(currentLoginDayCount.ToStringSeparated());
        }
    }
}
