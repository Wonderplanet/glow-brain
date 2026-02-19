using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionRewardAmountTextComponent : UIBehaviour
    {
        [SerializeField] UIText _amountText;

        public void SetAmountText(string amount)
        {
            _amountText.SetText(amount);
        }
    }
}
