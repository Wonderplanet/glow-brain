using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionProgressGaugeWithTextComponent : UIObject
    {
        [SerializeField] MissionProgressGaugeComponent _progressGaugeComponent;

        [SerializeField] UIText _currentValueText;

        [SerializeField] UIText _criterionValueText;

        public void SetProgressGaugeRate(float rate)
        {
            _progressGaugeComponent.SetProgressGaugeRate(rate);
        }

        public void SetProgressText(string current, string criterion)
        {
            _currentValueText.SetText(current);
            _criterionValueText.SetText(criterion);
        }
    }
}
